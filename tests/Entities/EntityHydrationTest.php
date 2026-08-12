<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Entities;

use PHPUnit\Framework\TestCase;
use Devcraft\Webshare\Entities\Proxy;
use Devcraft\Webshare\Entities\ApiKey;
use Devcraft\Webshare\Entities\SubUser;
use Devcraft\Webshare\Entities\ProxyStat;
use Devcraft\Webshare\Entities\ProxyConfig;
use Devcraft\Webshare\Entities\ProxyStatus;
use Devcraft\Webshare\Entities\Notification;
use Devcraft\Webshare\Entities\ProxyActivity;
use Devcraft\Webshare\Entities\DownloadToken;
use Devcraft\Webshare\Entities\ProxyListStats;
use Devcraft\Webshare\Entities\IpAuthorization;
use Devcraft\Webshare\Responses\ProxyList;
use Devcraft\Webshare\Responses\ApiKeyList;
use Devcraft\Webshare\Responses\SubUserList;
use Devcraft\Webshare\Responses\ProxyActivityList;
use Devcraft\Webshare\Responses\IpAuthorizationList;

final class EntityHydrationTest extends TestCase {

	public function testProxyFromArrayAndConnectionString(): void {
		$proxy = Proxy::fromArray([
			'id'            => '1',
			'username'      => 'user',
			'password'      => 'pass',
			'proxy_address' => '1.2.3.4',
			'port'          => 80,
			'country_code'  => 'US',
			'valid'         => true,
		]);

		$this->assertSame('user', $proxy->username);
		$this->assertSame(80, $proxy->port);
		$this->assertSame('http://user:pass@1.2.3.4:80', $proxy->getConnectionString());
		$this->assertSame(
			'http://user-us-city_los_angeles-rotate:pass@p.webshare.io:80',
			$proxy->getConnectionString(backbone: true, session: 'rotate', city: 'los_angeles'),
		);
	}

	public function testProxyListFromArray(): void {
		$list = ProxyList::fromArray([
			'count'    => 1,
			'next'     => NULL,
			'previous' => NULL,
			'results'  => [
				[
					'id'            => '9',
					'proxy_address' => '10.0.0.1',
					'port'          => 8080,
					'username'      => 'u',
					'password'      => 'p',
				],
			],
		]);

		$this->assertSame(1, $list->count);
		$this->assertCount(1, $list->results);
		$this->assertInstanceOf(Proxy::class, $list->results[0]);
		$this->assertSame('10.0.0.1', $list->results[0]->proxy_address);
	}

	public function testProxyConfigFromArray(): void {
		$config = ProxyConfig::fromArray([
			'id'                          => 1,
			'state'                       => 'completed',
			'username'                    => 'username',
			'request_timeout'             => 86400,
			'ip_authorization_country_codes' => ['US', 'FR'],
			'proxy_list_download_token'   => 'tok',
			'auto_replace_invalid_proxies'=> true,
		]);

		$this->assertSame(1, $config->id);
		$this->assertSame('completed', $config->state);
		$this->assertSame(['US', 'FR'], $config->ip_authorization_country_codes);
		$this->assertSame('tok', $config->proxy_list_download_token);
	}

	public function testProxyStatusAndListStatsFromArray(): void {
		$status = ProxyStatus::fromArray([
			'state'         => 'completed',
			'countries'     => ['US' => 5],
			'username'      => 'u',
			'is_proxy_used' => false,
		]);
		$stats = ProxyListStats::fromArray([
			'available_countries' => ['US' => 95],
			'ip_ranges_24'        => ['10.1.1.0/24' => 5],
		]);

		$this->assertSame('completed', $status->state);
		$this->assertSame(95, $stats->available_countries['US']);
	}

	public function testIpAuthorizationListFromArray(): void {
		$list = IpAuthorizationList::fromArray([
			'count'   => 1,
			'next'    => NULL,
			'previous'=> NULL,
			'results' => [
				[
					'id'         => 1337,
					'ip_address' => '10.1.2.3',
					'created_at' => '2022-06-14T11:58:10.246406-07:00',
					'last_used_at' => NULL,
				],
			],
		]);

		$this->assertInstanceOf(IpAuthorization::class, $list->results[0]);
		$this->assertSame(1337, $list->results[0]->id);
		$this->assertSame('10.1.2.3', $list->results[0]->ip_address);
	}

	public function testProxyStatAndActivityFromArray(): void {
		$stat = ProxyStat::fromArray([
			'timestamp'           => '2022-08-11T17:00:00-07:00',
			'bandwidth_total'     => 5000,
			'requests_total'      => 5,
			'requests_successful' => 4,
			'error_reasons'       => [
				[
					'reason'       => 'client_connect_forbidden_host',
					'type'         => 'configuration',
					'how_to_fix' => 'fix it',
					'http_status'  => 403,
					'count'        => 1,
				],
			],
			'countries_used' => ['GB' => 1],
		]);

		$activity = ProxyActivity::fromArray([
			'timestamp'     => '2022-08-16T15:29:42.517523-07:00',
			'protocol'      => 'http',
			'proxy_address' => '192.168.5.1',
			'bytes'         => 0,
			'hostname'      => 'ipv4.webshare.io',
			'port'          => 443,
		]);

		$list = ProxyActivityList::fromArray([
			'count'   => 1,
			'next'    => NULL,
			'previous'=> NULL,
			'results' => [
				[
					'timestamp'     => '2022-08-16T15:29:42.517523-07:00',
					'protocol'      => 'http',
					'proxy_address' => '192.168.5.1',
				],
			],
		]);

		$this->assertSame(5000, $stat->bandwidth_total);
		$this->assertCount(1, $stat->error_reasons);
		$this->assertSame('configuration', $stat->error_reasons[0]->type);
		$this->assertSame('http', $activity->protocol);
		$this->assertInstanceOf(ProxyActivity::class, $list->results[0]);
	}

	public function testDownloadTokenApiKeySubUserNotificationFromArray(): void {
		$token = DownloadToken::fromArray([
			'id'        => 56,
			'key'       => 'abcdefghijklmnopqrstuvwxyz',
			'scope'     => 'activity',
			'expire_at' => '2022-06-14T11:58:10.246406-07:00',
		]);
		$key = ApiKey::fromArray([
			'id'    => 1337,
			'key'   => 'abc1234',
			'label' => 'server1 key',
		]);
		$user = SubUser::fromArray([
			'id'          => 7,
			'label'       => 'Test User',
			'proxy_limit' => 10.0,
			'proxy_countries' => ['ZZ' => 1000],
		]);
		$notification = Notification::fromArray([
			'id'             => 1,
			'type'           => 'info',
			'is_dismissable' => true,
			'context'        => ['x' => 1],
		]);

		$keys = ApiKeyList::fromArray([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [['id' => 1, 'key' => 'k', 'label' => 'l']],
		]);
		$users = SubUserList::fromArray([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [['id' => 7, 'label' => 'u', 'proxy_limit' => 1.0]],
		]);

		$this->assertSame('activity', $token->scope);
		$this->assertSame('server1 key', $key->label);
		$this->assertSame(7, $user->id);
		$this->assertTrue($notification->is_dismissable);
		$this->assertInstanceOf(ApiKey::class, $keys->results[0]);
		$this->assertInstanceOf(SubUser::class, $users->results[0]);
	}

}
