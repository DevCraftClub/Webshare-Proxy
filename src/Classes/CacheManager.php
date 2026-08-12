<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Classes;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class CacheManager {

	/**
	 * @param list<string> $tags
	 *
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public static function set(mixed $value, ?string $key = NULL, ?int $ttl = NULL, array $tags = []): void {
		$cacheKey   = self::resolveCacheKey($key);
		$cachedItem = App::Cache()->getItem($cacheKey);
		$cachedItem->set($value);

		if($ttl !== NULL) {
			$cachedItem->expiresAfter($ttl);
		}

		if($tags !== [] && $cachedItem instanceof \Symfony\Component\Cache\CacheItem) {
			$cachedItem->tag($tags);
		}

		App::Cache()->save($cachedItem);
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public static function get(?string $key = NULL): mixed {
		$cacheKey   = self::resolveCacheKey($key);
		$cachedItem = App::Cache()->getItem($cacheKey);

		return $cachedItem->isHit() ? $cachedItem->get() : NULL;
	}

	/**
	 * @param list<string> $tags
	 */
	public static function remember(callable $callback, ?string $key = NULL, ?int $ttl = NULL, array $tags = []): mixed {
		$cacheKey = self::resolveCacheKey($key);

		if(App::Debug()) {
			$ttl = 5;
		}

		return App::Cache()->get($cacheKey, function(ItemInterface $item) use ($callback, $ttl, $tags) {
			if($ttl !== NULL) {
				$item->expiresAfter($ttl);
			}
			if($tags !== [] && method_exists($item, 'tag')) {
				$item->tag($tags);
			}

			return $callback();
		});
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public static function forget(?string $key = NULL): bool {
		return App::Cache()->deleteItem(self::resolveCacheKey($key));
	}

	/**
	 * @param list<string> $tags
	 */
	public static function invalidateTags(array $tags): void {
		$cache = App::Cache();
		if($cache instanceof TagAwareAdapter) {
			$cache->invalidateTags($tags);
		}
	}

	private static function resolveCacheKey(?string $key): string {
		if($key !== NULL && str_starts_with($key, 'http.')) {
			return self::hashKey($key);
		}

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2] ?? [];

		$parts = [
			$trace['class'] ?? 'global',
			$trace['function'] ?? 'unknown',
		];

		if($key !== NULL) {
			$parts[] = $key;
		}

		$parts = array_map([self::class, 'sanitizeKey'], $parts);

		return self::hashKey(implode('.', $parts));
	}

	private static function hashKey(string $rawKey): string {
		if(App::Debug()) {
			return strtolower(self::sanitizeKey($rawKey));
		}

		return hash('xxh128', strtolower($rawKey));
	}

	private static function sanitizeKey(mixed $key): string {
		$sanitized = filter_var((string) $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		return strtolower(str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '_', $sanitized));
	}

}
