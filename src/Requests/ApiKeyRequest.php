<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Classes\PageIterator;
use Devcraft\Webshare\Entities\ApiKey;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Responses\ApiKeyList;
use Devcraft\Webshare\Abstracts\AbstractRequest;

final class ApiKeyRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('apikey', ['ordering', 'label']);
	}

	public function list(): ApiKeyList {
		return ApiKeyList::fromArray(json_decode($this->request(), true));
	}

	/**
	 * @return \Generator<int, ApiKey>
	 */
	public function iterate(?int $maxPages = NULL): \Generator {
		return PageIterator::items(function(int $pageIndex) {
			$this->query()->withPage($pageIndex + 1);

			return $this->list();
		}, $maxPages);
	}

	public function retrieve(int $id): ApiKey {
		return ApiKey::fromArray(json_decode($this->request(additionalEndpoint: (string) $id), true));
	}

	public function create(string $label): ApiKey {
		$body = (new RequestBody())->withPostData('label', $label);

		return ApiKey::fromArray(json_decode($this->request(RequestMethod::POST, body: $body), true));
	}

	public function update(int $id, string $label): ApiKey {
		$body = (new RequestBody())->withPostData('label', $label);

		return ApiKey::fromArray(json_decode($this->request(RequestMethod::PATCH, (string) $id, $body), true));
	}

	public function delete(int $id): void {
		$this->request(RequestMethod::DELETE, (string) $id);
	}

}
