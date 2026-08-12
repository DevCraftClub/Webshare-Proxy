<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Entities\ProxyReplacement;
use Devcraft\Webshare\Responses\ProxyReplacementList;
use Devcraft\Webshare\Abstracts\AbstractRequest;

final class ProxyReplacementRequest extends AbstractRequest {

	public function __construct() {
		// ponytail: using v3 endpoint per docs, keeping query builder params broad based on general API patterns + plan_id.
		parent::__construct('proxy/replace', ['plan_id', 'ordering', 'dry_run', 'state']);
	}

	/**
	 * @throws \JsonException
	 */
	public function list(): ProxyReplacementList {
		return ProxyReplacementList::fromArray(json_decode($this->request(apiVersion: 'v3'), true));
	}

	/**
	 * @throws \JsonException
	 */
	public function retrieve(int $id): ProxyReplacement {
		return ProxyReplacement::fromArray(json_decode($this->request(additionalEndpoint: (string) $id, apiVersion: 'v3'), true));
	}

	/**
	 * @throws \JsonException
	 */
	public function create(array $to_replace, array $replace_with, bool $dry_run = false): ProxyReplacement {
		$body = (new RequestBody())->withPostData('to_replace', $to_replace)
		                           ->withPostData('replace_with', $replace_with)
		                           ->withPostData('dry_run', $dry_run);
		return ProxyReplacement::fromArray(json_decode($this->request(RequestMethod::POST, body: $body, apiVersion: 'v3'), true));
	}

}