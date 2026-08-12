<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Classes;

use RegexIterator;
use ReflectionClass;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Devcraft\Webshare\Abstracts\AbstractRequest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class App {

	public const API_URL        = 'https://proxy.webshare.io/api/';

	public const API_VERSION    = 'v2';

	public const CACHE_LIFETIME = 3600;

	public const ITERATE_MAX_PAGES = 100;

	public static bool $debug = false;

	private static ?Client                 $client            = NULL;
	private static ?TagAwareCacheInterface $cache             = NULL;
	private static ?string                 $apiKey            = NULL;
	private static ?string                 $apiUrl            = NULL;
	private static ?string                 $apiVersion        = NULL;
	private static int                     $cacheLifetime     = 0;
	private static ?string                 $cacheDirectory    = NULL;
	private static bool                    $cacheEnabled      = true;
	private static int                     $iterateMaxPages   = self::ITERATE_MAX_PAGES;

	public static function init(?string $apiKey = NULL, bool $force = false): void {
		static $initialized = false;

		if($initialized && !$force) {
			return;
		}

		if($apiKey) {
			self::$apiKey = $apiKey;
		} else {
			self::$apiKey = self::env('API_KEY');
		}
		if(!self::$apiKey) {
			throw new \RuntimeException('API_KEY environment variable is not set');
		}

		self::$apiUrl          = self::env('API_URL', self::API_URL);
		self::$apiVersion      = self::env('API_VERSION', self::API_VERSION);
		self::$cacheLifetime   = (int) (self::env('CACHE_LIFETIME', self::CACHE_LIFETIME));
		self::$cacheDirectory  = self::env('CACHE_DIRECTORY', dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'cache');
		self::$debug           = filter_var(self::env('DEBUG', false), FILTER_VALIDATE_BOOLEAN);
		self::$cacheEnabled    = filter_var(self::env('CACHE_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
		self::$iterateMaxPages = max(1, (int) self::env('ITERATE_MAX_PAGES', self::ITERATE_MAX_PAGES));

		$initialized = true;
	}

	/** @internal */
	public static function resetForTests(): void {
		self::$client         = NULL;
		self::$cache          = NULL;
		self::$apiKey         = NULL;
		self::$apiUrl         = NULL;
		self::$apiVersion     = NULL;
		self::$cacheDirectory = NULL;
		self::$cacheLifetime  = 0;
		self::$cacheEnabled   = true;
		self::$debug          = false;
		self::$iterateMaxPages = self::ITERATE_MAX_PAGES;
		self::init(self::env('API_KEY', 'test-key'), force: true);
	}

	public static function defineApiKey(string $apiKey): void {
		self::$apiKey = $apiKey;
	}

	public static function Cache(): TagAwareCacheInterface {
		self::init();

		if(self::$cache === NULL) {
			self::$cache = new TagAwareAdapter(
				new FilesystemAdapter('WebshareProxy', 0, self::$cacheDirectory),
			);
		}

		return self::$cache;
	}

	public static function Client(): Client {
		self::init();

		if(self::$client === NULL) {
			self::$client = self::newClient(self::$apiUrl, self::$apiVersion, self::$apiKey);
		}

		return self::$client;
	}

	public static function newClient(?string $url = NULL, ?string $version = NULL, ?string $key = NULL): Client {
		self::init();

		$url     ??= self::$apiUrl ?? self::API_URL;
		$version ??= self::$apiVersion ?? self::API_VERSION;

		if($key === NULL) {
			if(self::$apiKey === NULL) {
				throw new \RuntimeException('API_KEY variable is not set');
			}

			$key = self::$apiKey;
		}

		return new Client($key, $url, $version);
	}

	public static function Requests(string $requestType): AbstractRequest|null {
		$requests = CacheManager::remember(
			callback: fn() => self::loadRequests(),
			ttl     : 86400,
		);

		$requestClass = $requests[$requestType] ?? NULL;

		if($requestClass === NULL) {
			return NULL;
		}

		return new $requestClass();
	}

	public static function cacheEnabled(): bool {
		self::init();

		return self::$cacheEnabled;
	}

	public static function cacheLifetime(): int {
		self::init();

		return self::$cacheLifetime;
	}

	public static function iterateMaxPages(): int {
		self::init();

		return self::$iterateMaxPages;
	}

	public static function Debug(): bool {
		return self::$debug;
	}

	private static function env(string $key, mixed $default = NULL): mixed {
		if(array_key_exists($key, $_ENV)) {
			return $_ENV[$key];
		}

		$value = getenv($key);

		return $value === false ? $default : $value;
	}

	/**
	 * @throws \ReflectionException
	 */
	private static function loadRequests(): array {
		self::init();

		$requests    = [];
		$requestsDir = rtrim(dirname(__FILE__, 2), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Requests';

		if(!is_dir($requestsDir)) {
			throw new \RuntimeException("Requests directory not found: {$requestsDir}");
		}

		$dirIterator = new RecursiveDirectoryIterator($requestsDir, FilesystemIterator::SKIP_DOTS);
		$iterator    = new RecursiveIteratorIterator($dirIterator);
		$phpFiles    = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

		foreach($phpFiles as $file) {
			$filePath      = $file[0];
			$relativePath  = str_replace($requestsDir, '', $filePath);
			$classRelative = str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relativePath);
			$fqn           = rtrim('Devcraft\\Webshare\\Requests\\', '\\') . '\\' . ltrim($classRelative, '\\');

			if(class_exists($fqn, true) && is_subclass_of($fqn, AbstractRequest::class)) {
				$reflection = new ReflectionClass($fqn);

				if(!$reflection->isAbstract()) {
					$key            = str_replace('Request', '', $reflection->getShortName());
					$requests[$key] = $fqn;
				}
			}
		}

		return $requests;
	}

}
