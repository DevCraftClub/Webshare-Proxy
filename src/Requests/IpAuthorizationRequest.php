<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Classes\PageIterator;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Entities\IpAuthorization;
use Devcraft\Webshare\Abstracts\AbstractRequest;
use Devcraft\Webshare\Responses\IpAuthorizationList;

final class IpAuthorizationRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('proxy/ipauthorization', ['plan_id', 'ordering']);
	}

	public function list(): IpAuthorizationList {
		return IpAuthorizationList::fromArray(json_decode($this->request(), true));
	}

	/**
	 * @return \Generator<int, IpAuthorization>
	 */
	public function iterate(?int $maxPages = NULL): \Generator {
		return PageIterator::items(function(int $pageIndex) {
			$this->query()->withPage($pageIndex + 1);

			return $this->list();
		}, $maxPages);
	}

	public function retrieve(int $id): IpAuthorization {
		return IpAuthorization::fromArray(json_decode($this->request(additionalEndpoint: (string) $id), true));
	}

	public function create(string $ipAddress): IpAuthorization {
		$body = (new RequestBody())->withPostData('ip_address', $ipAddress);

		return IpAuthorization::fromArray(json_decode($this->request(RequestMethod::POST, body: $body), true));
	}

	public function delete(int $id): void {
		$this->request(RequestMethod::DELETE, (string) $id);
	}

	/**
	 * @return array{ip_address?: string}
	 */
	public function whatsMyIp(): array {
		$saved = $this->getEndpoint();
		$this->withEndpoint('proxy/ipauthorization/whatsmyip');
		try {
			return json_decode($this->request(), true) ?? [];
		} finally {
			$this->withEndpoint($saved);
		}
	}

}
