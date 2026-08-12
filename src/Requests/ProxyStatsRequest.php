<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Classes\PageIterator;
use Devcraft\Webshare\Entities\ProxyStat;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Abstracts\AbstractRequest;
use Devcraft\Webshare\Entities\ProxyActivity;
use Devcraft\Webshare\Responses\ProxyActivityList;

final class ProxyStatsRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('stats', [
			'plan_id',
			'timestamp__lte',
			'timestamp__gte',
			'search',
			'error_reason',
			'bytes__gte',
			'bytes__lte',
			'verification_category',
			'download_token',
		]);
	}

	/**
	 * Hourly stats list (not paginated by the API).
	 *
	 * @return list<ProxyStat>
	 */
	public function list(): array {
		$decoded = json_decode($this->request(), true) ?? [];
		if(!is_array($decoded)) {
			return [];
		}

		return array_map(
			static fn(array $row) => ProxyStat::fromArray($row),
			array_values(array_filter($decoded, 'is_array')),
		);
	}

	public function aggregate(): ProxyStat {
		$saved = $this->getEndpoint();
		$this->withEndpoint('stats/aggregate');
		try {
			return ProxyStat::fromArray(json_decode($this->request(), true));
		} finally {
			$this->withEndpoint($saved);
		}
	}

	public function listActivities(): ProxyActivityList {
		$saved = $this->getEndpoint();
		$this->withEndpoint('proxy/activity');
		try {
			return ProxyActivityList::fromArray(json_decode($this->request(), true));
		} finally {
			$this->withEndpoint($saved);
		}
	}

	/**
	 * @return \Generator<int, ProxyActivity>
	 */
	public function iterateActivities(?int $maxPages = NULL): \Generator {
		$lastTimestamp = NULL;

		return PageIterator::items(function() use (&$lastTimestamp) {
			if($lastTimestamp !== NULL) {
				$this->query()->withStartingAfter($lastTimestamp);
			}

			$list = $this->listActivities();
			if($list->results !== []) {
				$last = $list->results[array_key_last($list->results)];
				if($last instanceof ProxyActivity && $last->timestamp !== NULL) {
					$lastTimestamp = $last->timestamp;
				}
			}

			return $list;
		}, $maxPages);
	}

	public function downloadActivities(): string {
		$saved = $this->getEndpoint();
		$this->withEndpoint('proxy/activity/download');
		try {
			return $this->request(RequestMethod::GET);
		} finally {
			$this->withEndpoint($saved);
		}
	}

}
