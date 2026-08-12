<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Responses\ReplacedProxyList;
use Devcraft\Webshare\Abstracts\AbstractRequest;

final class ReplacedProxyRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('proxy/list/replaced', ['plan_id', 'proxy_list_replacement']);
	}

	public function list(): ReplacedProxyList {
		return ReplacedProxyList::fromArray(json_decode($this->request(), true));
	}

	public function download(string $token, string $countryCodes = '-', string $authMethod = 'username', string $mode = 'direct', string $search = '-'): string {
		// ponytail: docs show download via GET with query params or path params, standardizing to path params to match existing ProxyRequest style.
		return $this->request(RequestMethod::GET, sprintf('download/%s/%s/any/%s/%s/%s/', $token, $countryCodes, $authMethod, $mode, urlencode($search)));
	}

}