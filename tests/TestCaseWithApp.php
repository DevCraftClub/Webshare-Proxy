<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\TestCase;
use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Classes\Client;

abstract class TestCaseWithApp extends TestCase {

	protected string $cacheDir;

	protected function setUp(): void {
		parent::setUp();
		$this->cacheDir = sys_get_temp_dir() . '/webshare-test-cache-' . uniqid('', true);
		mkdir($this->cacheDir);
		$_ENV['API_KEY'] = 'test-key';
		$_ENV['API_URL'] = 'https://example.com/api/';
		$_ENV['API_VERSION'] = 'v2';
		$_ENV['CACHE_DIRECTORY'] = $this->cacheDir;
		$_ENV['CACHE_LIFETIME'] = '3600';
		$_ENV['CACHE_ENABLED'] = '1';
		$_ENV['ITERATE_MAX_PAGES'] = '100';
		$_ENV['DEBUG'] = '0';
		App::resetForTests();
	}

	protected function tearDown(): void {
		$this->removeDir($this->cacheDir);
		parent::tearDown();
	}

	protected function injectMockClient(MockHandler $mock, string $version = 'v2'): Client {
		$client = new Client(
			'test-key',
			'https://example.com/api/',
			$version,
			new GuzzleClient(['handler' => HandlerStack::create($mock)]),
		);

		$property = new \ReflectionProperty(App::class, 'client');
		$property->setValue(NULL, $client);

		return $client;
	}

	protected function removeDir(string $dir): void {
		if(!is_dir($dir)) {
			return;
		}
		foreach(scandir($dir) ?: [] as $item) {
			if($item === '.' || $item === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			is_dir($path) ? $this->removeDir($path) : unlink($path);
		}
		rmdir($dir);
	}

}
