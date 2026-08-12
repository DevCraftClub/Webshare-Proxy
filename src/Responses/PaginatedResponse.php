<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\Filter;
use Devcraft\Abstracts\AbstractReflection;

class PaginatedResponse extends AbstractReflection {
	/**
	 * Total number of elements which are paginated.
	 *
	 * @var int|null
	 */
	public ?int    $count    = NULL;

	/**
	 * Full URL to the next page API resource. If there is no next page, set to null.
	 *
	 * @var string|null
	 */
	#[Filter(FILTER_VALIDATE_URL)]
	public ?string $next     = NULL;

	/**
	 * Full URL to the previous page API resource. If there is no previous page, set to null. If a page is using starting_after for pagination, previous field will not be available.
	 *
	 * @var string|null
	 */
	#[Filter(FILTER_VALIDATE_URL)]
	public ?string $previous = NULL;
	/**
	 * List of elements which are paginated. If page_size is 25, you can expect up to 25 elements in the results.
	 *
	 * @var array
	 */
	public array $results = [];
}