<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Classes;

use Lombok\Getter;
use Lombok\Setter;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client as GuzzleClient;
use JetBrains\PhpStorm\ExpectedValues;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Exceptions\ApiException;
use Devcraft\Webshare\Abstracts\AbstractBasicClass;
use Devcraft\Webshare\Exceptions\RateLimitException;

#[Setter, Getter]
class Client extends AbstractBasicClass {

	private const MAX_RETRIES     = 3;
	private const BACKOFF_MIN_SEC = 1;
	private const BACKOFF_MAX_SEC = 120;

	private GuzzleClient $httpClient;
	private array        $rateLimits = [];
	private ?string      $subuserId  = NULL;

	public function __construct(
		private string $apiKey,
		private string $apiUrl,
		private string $apiVersion,
		?GuzzleClient $httpClient = NULL,
	) {
		parent::__construct();

		if(empty($this->apiKey)) {
			throw new \InvalidArgumentException('API key must not be empty');
		}

		$this->httpClient = $httpClient ?? new GuzzleClient([
			'base_uri' => rtrim($this->apiUrl, '/') . '/',
			'headers'  => [
				'Authorization' => 'Token ' . $this->apiKey,
			],
		]);
	}

	public function withSubuser(?string $subuserId): self {
		$this->subuserId = $subuserId;

		return $this;
	}

	public function getSubuser(): ?string {
		return $this->subuserId;
	}

	/**
	 * Send a request to the WebShare API.
	 *
	 * @throws ApiException on 4xx/5xx responses
	 * @throws RateLimitException|\JsonException when retries exhausted after 429
	 */
	public function send(
		string        $endpoint,
		#[ExpectedValues(valuesFromClass: RequestMethod::class)]
		RequestMethod $method = RequestMethod::GET,
		?QueryBuilder $query = NULL,
		?RequestBody  $body = NULL,
		bool          $forceRefresh = false,
	): string {
		$url     = $this->resolveUrl($endpoint, $query);
		$options = [
			RequestOptions::HEADERS => $this->buildHeaders($body),
		];

		if($body !== NULL && in_array($method, [RequestMethod::POST, RequestMethod::PUT, RequestMethod::PATCH], true)) {
			$body->build();

			match (true) {
				$body->multipart !== [] => $options[RequestOptions::MULTIPART] = $body->multipart,
				$body->postData !== []  => $options[RequestOptions::JSON] = $body->postData,
				default                 => $options[RequestOptions::BODY] = $body->body,
			};
		}

		$cacheable = $this->isCacheable($method, $endpoint);
		$cacheKey  = $cacheable ? $this->cacheKey($method, $url) : NULL;
		$cacheTag  = $this->cacheTag($endpoint);

		if($cacheable && !$forceRefresh && App::cacheEnabled()) {
			$cached = CacheManager::get($cacheKey);
			if(is_string($cached)) {
				return $cached;
			}
		}

		$attempt   = 0;
		$backoffMs = 1000;

		while(true) {
			try {
				$response = $this->httpClient->request($method->value, $url, $options);
				$this->trackRateLimits($response->getHeaders());
				$result = $response->getBody()->getContents();

				if($cacheable && App::cacheEnabled() && $cacheKey !== NULL) {
					CacheManager::set($result, $cacheKey, App::cacheLifetime(), [$cacheTag]);
				}

				if($method !== RequestMethod::GET && App::cacheEnabled()) {
					CacheManager::invalidateTags([$cacheTag]);
				}

				return $result;
			} catch(RequestException $e) {
				$response = $e->getResponse();
				if($response === NULL) {
					throw new ApiException('Network error: ' . $e->getMessage(), 0, $e);
				}

				$status       = $response->getStatusCode();
				$responseBody = (string) $response->getBody();

				if($status === 429 || $status >= 500) {
					$attempt++;
					if($attempt >= self::MAX_RETRIES) {
						$status === 429
							? throw new RateLimitException($this->parseRetryAfter($response->getHeaders()), $e)
							: throw new ApiException("HTTP $status: " . ($responseBody !== '' ? $responseBody : $e->getMessage()), $status, $e);
					}
					usleep($this->calculateBackoff($backoffMs, $response->getHeaders()) * 1000);
					$backoffMs *= 2;
					continue;
				}

				if($status === 400) {
					throw ApiException::fromValidationResponse($responseBody, $status);
				}

				throw new ApiException("HTTP $status: " . ($responseBody !== '' ? $responseBody : $e->getMessage()), $status, $e);
			} catch(GuzzleException $e) {
				throw new ApiException('HTTP error: ' . $e->getMessage(), 0, $e);
			}
		}
	}

	/**
	 * @return array{remaining?: int, reset?: int}
	 */
	public function getRateLimits(): array {
		return $this->rateLimits;
	}

	public function getApiVersion(): string {
		return $this->apiVersion;
	}

	private function buildHeaders(?RequestBody $body): array {
		$headers = $body?->header ?? [];

		if($this->subuserId !== NULL && $this->subuserId !== '') {
			$headers['X-Subuser'] = $this->subuserId;
		}

		return $headers;
	}

	private function resolveUrl(string $endpoint, ?QueryBuilder $query = NULL): string {
		$path        = $this->apiVersion . '/' . ltrim($endpoint, '/');
		$queryString = $query?->build() ?? '';

		return $queryString !== '' ? $path . '?' . $queryString : $path;
	}

	private function isCacheable(RequestMethod $method, string $endpoint): bool {
		if($method !== RequestMethod::GET) {
			return false;
		}

		return !str_contains('/' . ltrim($endpoint, '/') . '/', '/download/');
	}

	private function cacheKey(RequestMethod $method, string $url): string {
		$subuser = $this->subuserId ?? '';

		return 'http.' . $method->value . '.' . $url . '.sub:' . $subuser;
	}

	private function cacheTag(string $endpoint): string {
		$parts = explode('/', trim($endpoint, '/'));

		return 'http.' . ($parts[0] !== '' ? $parts[0] : 'root');
	}

	private function trackRateLimits(array $headers): void {
		$headers = array_change_key_case($headers);

		if(isset($headers['x-ratelimit-remaining'][0])) {
			$this->rateLimits['remaining'] = (int) $headers['x-ratelimit-remaining'][0];
		}
		if(isset($headers['x-ratelimit-reset'][0])) {
			$this->rateLimits['reset'] = (int) $headers['x-ratelimit-reset'][0];
		}
	}

	private function calculateBackoff(int $baseMs, array $headers): int {
		return max($baseMs, $this->parseRetryAfter($headers) * 1000);
	}

	/**
	 * Prefer Retry-After (seconds). Treat large x-ratelimit-reset values as unix timestamps.
	 */
	private function parseRetryAfter(array $headers): int {
		$headers = array_change_key_case($headers);

		if(isset($headers['retry-after'][0])) {
			return $this->clampBackoffSeconds((int) $headers['retry-after'][0]);
		}

		if(isset($headers['x-ratelimit-reset'][0])) {
			$reset = (int) $headers['x-ratelimit-reset'][0];
			$seconds = $reset > time() ? $reset - time() : $reset;

			return $this->clampBackoffSeconds($seconds);
		}

		return 60;
	}

	private function clampBackoffSeconds(int $seconds): int {
		return max(self::BACKOFF_MIN_SEC, min(self::BACKOFF_MAX_SEC, $seconds));
	}

}
