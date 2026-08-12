<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Classes\PageIterator;
use Devcraft\Webshare\Entities\SubUser;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Responses\SubUserList;
use Devcraft\Webshare\Abstracts\AbstractRequest;

final class SubUserRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('subuser', ['plan_id', 'ordering', 'label']);
	}

	public function list(): SubUserList {
		return SubUserList::fromArray(json_decode($this->request(), true));
	}

	/**
	 * @return \Generator<int, SubUser>
	 */
	public function iterate(?int $maxPages = NULL): \Generator {
		return PageIterator::items(function(int $pageIndex) {
			$this->query()->withPage($pageIndex + 1);

			return $this->list();
		}, $maxPages);
	}

	public function retrieve(int $id): SubUser {
		return SubUser::fromArray(json_decode($this->request(additionalEndpoint: (string) $id), true));
	}

	public function create(string $label, ?float $proxyLimit = NULL, ?int $maxThreadCount = NULL): SubUser {
		$body = (new RequestBody())->withPostData('label', $label);
		if($proxyLimit !== NULL) {
			$body->withPostData('proxy_limit', $proxyLimit);
		}
		if($maxThreadCount !== NULL) {
			$body->withPostData('max_thread_count', $maxThreadCount);
		}

		return SubUser::fromArray(json_decode($this->request(RequestMethod::POST, body: $body), true));
	}

	public function update(int $id, array $fields): SubUser {
		$body = new RequestBody();
		foreach($fields as $key => $value) {
			$body->withPostData((string) $key, $value);
		}

		return SubUser::fromArray(json_decode($this->request(RequestMethod::PATCH, (string) $id, $body), true));
	}

	public function delete(int $id): void {
		$this->request(RequestMethod::DELETE, (string) $id);
	}

	public function refreshProxyList(int $id): SubUser {
		return SubUser::fromArray(json_decode($this->request(RequestMethod::POST, $id . '/refresh/'), true));
	}

	/**
	 * Masquerade subsequent App::Client() calls as this sub-user (X-Subuser header).
	 */
	public function masqueradeAs(int|string|null $subuserId): void {
		App::Client()->withSubuser($subuserId === NULL ? NULL : (string) $subuserId);
	}

}
