<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Abstracts;

use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Classes\Client;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Classes\QueryBuilder;
use Devcraft\Webshare\Entities\RequestBody;

abstract class AbstractRequest extends AbstractBasicClass {

	private QueryBuilder $queryBuilder;
	private string       $endpoint;

	public function __construct(string $endpoint, array $parameters, array $requiredFields = []) {
		parent::__construct();

		$this->endpoint     = $endpoint;
		$this->queryBuilder = (new QueryBuilder())->withOptionalFields($parameters)->withRequiredFields($requiredFields);
	}

	public function query(): QueryBuilder {
		return $this->queryBuilder;
	}

	public function updateQuery(QueryBuilder $queryBuilder): void {
		$this->queryBuilder = $queryBuilder;
	}

	public function setEndpoint(string $endpoint): void {
		$this->endpoint = $endpoint;
	}

	public function withEndpoint(string $endpoint): self {
		$this->endpoint = $endpoint;

		return $this;
	}

	public function getEndpoint(): string {
		return $this->endpoint;
	}

	/**
	 * @throws \JsonException
	 */
	public function request(
		RequestMethod $method = RequestMethod::GET,
		?string       $additionalEndpoint = NULL,
		?RequestBody  $body = NULL,
		?string       $apiVersion = NULL,
		bool          $forceRefresh = false,
	): string {
		$endpoint = $this->endpoint;
		if($additionalEndpoint !== NULL) {
			$endpoint = rtrim($endpoint, '/') . '/' . $additionalEndpoint;
		}

		$apiVersion ??= App::Client()->getApiVersion();

		return $this->defineClient($apiVersion)->send($endpoint, $method, $this->queryBuilder, $body, $forceRefresh);
	}

	private function defineClient(string $apiVersion): Client {
		$client = App::Client();
		if($apiVersion === $client->getApiVersion()) {
			return $client;
		}

		return App::newClient(version: $apiVersion);
	}

}
