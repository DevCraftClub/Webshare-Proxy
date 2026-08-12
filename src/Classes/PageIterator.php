<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Classes;

use Devcraft\Webshare\Exceptions\ApiException;
use Devcraft\Webshare\Responses\PaginatedResponse;

/**
 * Yields items across paginated list responses with a hard page cap.
 */
final class PageIterator {

	/**
	 * @param callable(int $pageIndex): PaginatedResponse $fetchPage 0-based page index; caller sets QueryBuilder page/starting_after
	 *
	 * @return \Generator<int, mixed>
	 */
	public static function items(callable $fetchPage, ?int $maxPages = NULL): \Generator {
		$maxPages ??= App::iterateMaxPages();
		$maxPages = max(1, $maxPages);

		for($pageIndex = 0; $pageIndex < $maxPages; $pageIndex++) {
			$response = $fetchPage($pageIndex);
			if(!$response instanceof PaginatedResponse) {
				throw new ApiException('PageIterator expects a PaginatedResponse from fetchPage.');
			}

			foreach($response->results as $item) {
				yield $item;
			}

			if($response->next === NULL) {
				return;
			}
		}

		throw new ApiException("PageIterator exceeded max pages ({$maxPages}).");
	}

}
