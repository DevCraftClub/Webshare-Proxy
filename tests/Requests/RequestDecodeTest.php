<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Requests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Devcraft\Webshare\Tests\TestCaseWithApp;
use Devcraft\Webshare\Requests\ProxyRequest;
use Devcraft\Webshare\Requests\ApiKeyRequest;
use Devcraft\Webshare\Requests\SubUserRequest;
use Devcraft\Webshare\Requests\ProfileRequest;
use Devcraft\Webshare\Requests\ProxyStatsRequest;
use Devcraft\Webshare\Requests\NotificationRequest;
use Devcraft\Webshare\Requests\ProxyConfigRequest;
use Devcraft\Webshare\Requests\DownloadTokenRequest;
use Devcraft\Webshare\Requests\IpAuthorizationRequest;
use Devcraft\Webshare\Entities\Proxy;
use Devcraft\Webshare\Entities\ApiKey;
use Devcraft\Webshare\Entities\SubUser;
use Devcraft\Webshare\Entities\Profile;
use Devcraft\Webshare\Entities\ProxyStat;
use Devcraft\Webshare\Entities\ProxyConfig;
use Devcraft\Webshare\Entities\Notification;
use Devcraft\Webshare\Entities\DownloadToken;
use Devcraft\Webshare\Entities\IpAuthorization;

final class RequestDecodeTest extends TestCaseWithApp {

	public function testProxyGetListDecodesResults(): void {
		$payload = json_encode([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [[
				'id' => '1', 'username' => 'u', 'password' => 'p',
				'proxy_address' => '1.2.3.4', 'port' => 80, 'country_code' => 'US',
			]],
		], JSON_THROW_ON_ERROR);
		$this->injectMockClient(new MockHandler([new Response(200, [], $payload)]));

		$req = new ProxyRequest();
		$req->query()->withFilter('mode', 'direct');
		$list = $req->getList();

		$this->assertCount(1, $list->results);
		$this->assertInstanceOf(Proxy::class, $list->results[0]);
		$this->assertSame('1.2.3.4', $list->results[0]->proxy_address);
	}

	public function testIpAuthorizationListAndCreate(): void {
		$listBody = json_encode([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [['id' => 1, 'ip_address' => '10.0.0.1', 'created_at' => 't', 'last_used_at' => NULL]],
		], JSON_THROW_ON_ERROR);
		$createBody = json_encode([
			'id' => 2, 'ip_address' => '10.0.0.2', 'created_at' => 't', 'last_used_at' => NULL,
		], JSON_THROW_ON_ERROR);
		$whatsMyIp = json_encode(['ip_address' => '9.9.9.9'], JSON_THROW_ON_ERROR);

		$this->injectMockClient(new MockHandler([
			new Response(200, [], $listBody),
			new Response(200, [], $createBody),
			new Response(200, [], $whatsMyIp),
		]));

		$req = new IpAuthorizationRequest();
		$list = $req->list();
		$created = $req->create('10.0.0.2');
		$mine = $req->whatsMyIp();

		$this->assertInstanceOf(IpAuthorization::class, $list->results[0]);
		$this->assertSame(2, $created->id);
		$this->assertSame('9.9.9.9', $mine['ip_address']);
	}

	public function testNotificationListAndRetrieve(): void {
		$listBody = json_encode([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [['id' => 5, 'type' => 'info', 'is_dismissable' => true]],
		], JSON_THROW_ON_ERROR);
		$one = json_encode(['id' => 5, 'type' => 'info', 'is_dismissable' => true], JSON_THROW_ON_ERROR);

		$this->injectMockClient(new MockHandler([
			new Response(200, [], $listBody),
			new Response(200, [], $one),
		]));

		$req = new NotificationRequest();
		$list = $req->list();
		$item = $req->retrieve(5);

		$this->assertInstanceOf(Notification::class, $list->results[0]);
		$this->assertSame(5, $item->id);
	}

	public function testProfileRetrieve(): void {
		$body = json_encode([
			'id' => 1, 'email' => 'a@b.c', 'first_name' => 'A', 'last_name' => 'B',
		], JSON_THROW_ON_ERROR);
		$this->injectMockClient(new MockHandler([new Response(200, [], $body)]));

		$profile = (new ProfileRequest())->retrieve();
		$this->assertInstanceOf(Profile::class, $profile);
	}

	public function testProxyStatsListAndAggregate(): void {
		$listBody = json_encode([[
			'timestamp' => '2022-08-11T17:00:00-07:00',
			'bandwidth_total' => 5000,
			'requests_total' => 5,
			'error_reasons' => [],
		]], JSON_THROW_ON_ERROR);
		$aggBody = json_encode([
			'bandwidth_total' => 5000,
			'requests_total' => 5,
			'error_reasons' => [],
		], JSON_THROW_ON_ERROR);

		$this->injectMockClient(new MockHandler([
			new Response(200, [], $listBody),
			new Response(200, [], $aggBody),
		]));

		$req = new ProxyStatsRequest();
		$list = $req->list();
		$agg = $req->aggregate();

		$this->assertCount(1, $list);
		$this->assertInstanceOf(ProxyStat::class, $list[0]);
		$this->assertSame(5000, $agg->bandwidth_total);
	}

	public function testDownloadTokenGet(): void {
		$body = json_encode([
			'id' => 56, 'key' => 'abc', 'scope' => 'activity', 'expire_at' => 't',
		], JSON_THROW_ON_ERROR);
		$this->injectMockClient(new MockHandler([new Response(200, [], $body)]));

		$token = (new DownloadTokenRequest())->get('activity');
		$this->assertInstanceOf(DownloadToken::class, $token);
		$this->assertSame('activity', $token->scope);
	}

	public function testApiKeyListAndCreate(): void {
		$listBody = json_encode([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [['id' => 1, 'key' => 'k', 'label' => 'l']],
		], JSON_THROW_ON_ERROR);
		$createBody = json_encode([
			'id' => 2, 'key' => 'new', 'label' => 'server',
		], JSON_THROW_ON_ERROR);

		$this->injectMockClient(new MockHandler([
			new Response(200, [], $listBody),
			new Response(200, [], $createBody),
		]));

		$req = new ApiKeyRequest();
		$list = $req->list();
		$created = $req->create('server');

		$this->assertInstanceOf(ApiKey::class, $list->results[0]);
		$this->assertSame('server', $created->label);
	}

	public function testSubUserListAndCreate(): void {
		$listBody = json_encode([
			'count' => 1, 'next' => NULL, 'previous' => NULL,
			'results' => [['id' => 7, 'label' => 'Test', 'proxy_limit' => 10.0]],
		], JSON_THROW_ON_ERROR);
		$createBody = json_encode([
			'id' => 8, 'label' => 'new', 'proxy_limit' => 5.0, 'max_thread_count' => 100,
		], JSON_THROW_ON_ERROR);

		$this->injectMockClient(new MockHandler([
			new Response(200, [], $listBody),
			new Response(200, [], $createBody),
		]));

		$req = new SubUserRequest();
		$list = $req->list();
		$created = $req->create('new', 5.0, 100);

		$this->assertInstanceOf(SubUser::class, $list->results[0]);
		$this->assertSame(8, $created->id);
	}

	public function testProxyConfigRetrieveUsesV3Client(): void {
		$body = json_encode([
			'id' => 1,
			'state' => 'completed',
			'username' => 'u',
			'proxy_list_download_token' => 'tok',
		], JSON_THROW_ON_ERROR);

		// retrieve() forces apiVersion v3 → newClient; inject v3 as singleton by matching version.
		$_ENV['API_VERSION'] = 'v3';
		\Devcraft\Webshare\Classes\App::resetForTests();
		$this->injectMockClient(new MockHandler([new Response(200, [], $body)]), 'v3');

		$req = new ProxyConfigRequest();
		$req->withPlanId(1);
		$config = $req->retrieve();

		$this->assertInstanceOf(ProxyConfig::class, $config);
		$this->assertSame('tok', $config->proxy_list_download_token);
	}

	public function testSubUserMasqueradeSetsClientHeaderState(): void {
		$this->injectMockClient(new MockHandler([]));
		$req = new SubUserRequest();
		$req->masqueradeAs(42);
		$this->assertSame('42', \Devcraft\Webshare\Classes\App::Client()->getSubuser());
		$req->masqueradeAs(NULL);
		$this->assertNull(\Devcraft\Webshare\Classes\App::Client()->getSubuser());
	}

}
