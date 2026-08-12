<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Classes;

use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Tests\TestCaseWithApp;

final class AppConfigTest extends TestCaseWithApp {

	public function testIterateMaxPagesAndCacheFlagsFromEnv(): void {
		$_ENV['ITERATE_MAX_PAGES'] = '7';
		$_ENV['CACHE_ENABLED'] = '0';
		$_ENV['CACHE_LIFETIME'] = '120';
		App::resetForTests();

		$this->assertSame(7, App::iterateMaxPages());
		$this->assertFalse(App::cacheEnabled());
		$this->assertSame(120, App::cacheLifetime());
	}

	public function testRequestsDiscoversProxyRequest(): void {
		$req = App::Requests('Proxy');
		$this->assertInstanceOf(\Devcraft\Webshare\Requests\ProxyRequest::class, $req);
		$this->assertNull(App::Requests('DoesNotExist'));
	}

}
