<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Classes;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\TestCase;
use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Classes\Client;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Exceptions\RateLimitException;

final class ClientTest extends TestCase {

	private string $cacheDir;

	protected function setUp(): void {
		$this->cacheDir = sys_get_temp_dir() . '/webshare-test-cache-' . uniqid('', true);
		mkdir($this->cacheDir);
		$_ENV['API_KEY'] = 'test-key';
		$_ENV['CACHE_DIRECTORY'] = $this->cacheDir;
		$_ENV['CACHE_LIFETIME'] = '3600';
		$_ENV['CACHE_ENABLED'] = '1';
		$_ENV['DEBUG'] = '0';
		App::resetForTests();
	}

	protected function tearDown(): void {
		$this->removeDir($this->cacheDir);
	}

	public function testGetWithoutBodyDoesNotTypeError(): void {
		$mock = new MockHandler([new Response(200, [], '{"ok":true}')]);
		$client = new Client('test-key', 'https://example.com/api/', 'v2', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

		$this->assertSame('{"ok":true}', $client->send('profile'));
	}

	public function testGetResponseIsCached(): void {
		$mock = new MockHandler([
			new Response(200, [], '{"page":1}'),
			new Response(200, [], '{"page":2}'),
		]);
		$client = new Client('test-key', 'https://example.com/api/', 'v2', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

		$first = $client->send('profile');
		$second = $client->send('profile');

		$this->assertSame('{"page":1}', $first);
		$this->assertSame('{"page":1}', $second);
		$this->assertSame(1, $mock->count());
	}

	public function testForceRefreshBypassesCache(): void {
		$mock = new MockHandler([
			new Response(200, [], '{"page":1}'),
			new Response(200, [], '{"page":2}'),
		]);
		$client = new Client('test-key', 'https://example.com/api/', 'v2', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

		$client->send('profile');
		$second = $client->send('profile', forceRefresh: true);

		$this->assertSame('{"page":2}', $second);
	}

	public function testDownloadPathIsNotCached(): void {
		$mock = new MockHandler([
			new Response(200, [], 'download-1'),
			new Response(200, [], 'download-2'),
		]);
		$client = new Client('test-key', 'https://example.com/api/', 'v2', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

		$this->assertSame('download-1', $client->send('proxy/list/download/token/-/any/username/direct/-/'));
		$this->assertSame('download-2', $client->send('proxy/list/download/token/-/any/username/direct/-/'));
	}

	public function testMutationInvalidatesCacheTag(): void {
		$mock = new MockHandler([
			new Response(200, [], '{"v":1}'),
			new Response(204, [], ''),
			new Response(200, [], '{"v":2}'),
		]);
		$client = new Client('test-key', 'https://example.com/api/', 'v2', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

		$this->assertSame('{"v":1}', $client->send('notification'));
		$client->send('notification/1/dismiss/', RequestMethod::POST);
		$this->assertSame('{"v":2}', $client->send('notification'));
	}

	public function testParseRetryAfterTreatsUnixResetAsDeltaAndClamps(): void {
		$client = new Client('test-key', 'https://example.com/api/', 'v2', new GuzzleClient(['handler' => HandlerStack::create(new MockHandler())]));
		$method = new \ReflectionMethod(Client::class, 'parseRetryAfter');

		$seconds = $method->invoke($client, [
			'x-ratelimit-reset' => [(string) (time() + 99999)],
		]);
		$this->assertSame(120, $seconds);

		$seconds = $method->invoke($client, [
			'retry-after' => ['5'],
			'x-ratelimit-reset' => [(string) (time() + 99999)],
		]);
		$this->assertSame(5, $seconds);

		$seconds = $method->invoke($client, [
			'x-ratelimit-reset' => ['30'],
		]);
		$this->assertSame(30, $seconds);
	}

	private function removeDir(string $dir): void {
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
