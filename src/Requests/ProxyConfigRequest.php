<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Entities\ProxyConfig;
use Devcraft\Webshare\Entities\ProxyStatus;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Entities\ProxyListStats;
use Devcraft\Webshare\Abstracts\AbstractRequest;

/**
 * Proxy configuration / status / list-stats (API v3).
 */
final class ProxyConfigRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('proxy/config', ['plan_id'], ['plan_id']);
	}

	public function withPlanId(int|string $planId): self {
		$this->query()->withFilter('plan_id', $planId);

		return $this;
	}

	public function retrieve(): ProxyConfig {
		return ProxyConfig::fromArray(json_decode($this->request(apiVersion: 'v3'), true));
	}

	public function update(array $fields): ProxyConfig {
		$body = new RequestBody();
		foreach($fields as $key => $value) {
			$body->withPostData((string) $key, $value);
		}

		// Docs: PATCH /api/v2/proxy/config/ (get/status/stats use v3).
		return ProxyConfig::fromArray(json_decode($this->request(RequestMethod::PATCH, body: $body, apiVersion: 'v2'), true));
	}

	public function status(): ProxyStatus {
		return ProxyStatus::fromArray(json_decode($this->requestAt('proxy/list/status', apiVersion: 'v3'), true));
	}

	public function listStats(): ProxyListStats {
		return ProxyListStats::fromArray(json_decode($this->requestAt('proxy/list/stats', apiVersion: 'v3'), true));
	}

	public function assignUnallocatedCountries(array $countries): ProxyConfig {
		$body = (new RequestBody())->withPostData('countries', $countries);

		return ProxyConfig::fromArray(json_decode(
			$this->requestAt('proxy/config/assign_unallocated_countries', RequestMethod::POST, body: $body, apiVersion: 'v3'),
			true,
		));
	}

	private function requestAt(
		string         $endpoint,
		RequestMethod  $method = RequestMethod::GET,
		?RequestBody   $body = NULL,
		?string        $apiVersion = NULL,
	): string {
		$savedEndpoint = $this->getEndpoint();
		$this->withEndpoint($endpoint);
		try {
			return $this->request($method, body: $body, apiVersion: $apiVersion);
		} finally {
			$this->withEndpoint($savedEndpoint);
		}
	}

}
