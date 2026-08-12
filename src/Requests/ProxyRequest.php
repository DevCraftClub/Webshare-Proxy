<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Classes\PageIterator;
use Devcraft\Webshare\Entities\Proxy;
use Devcraft\Webshare\Responses\ProxyList;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Abstracts\AbstractRequest;

class ProxyRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('proxy/list',
			['country_code', 'country_code__in', 'search', 'ordering', 'created_at', 'proxy_address', 'proxy_address__in', 'valid', 'asn_number', 'asn_name', 'plan_id'],
			['mode']);
	}

	public function getList(): ProxyList {
		return ProxyList::fromArray(json_decode($this->request(), true));
	}

	/**
	 * @return \Generator<int, Proxy>
	 */
	public function iterate(?int $maxPages = NULL): \Generator {
		return PageIterator::items(function(int $pageIndex) {
			$this->query()->withPage($pageIndex + 1);

			return $this->getList();
		}, $maxPages);
	}

	public function downloadList(string $token, string $countryCodes = '-', string $authMethod = 'username', string $mode = 'direct', string $search = '-'): string {
		return $this->request(RequestMethod::GET, sprintf('download/%s/%s/any/%s/%s/%s/', $token, $countryCodes, $authMethod, $mode, urlencode($search)));
	}

	public function refresh(): void {
		$this->request(RequestMethod::POST, 'refresh/');
	}

}
