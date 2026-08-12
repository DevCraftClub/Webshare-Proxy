<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\ApiKey;

final class ApiKeyList extends PaginatedResponse {

	/**
	 * @var list<ApiKey>
	 */
	#[ArrayOf(ApiKey::class)]
	public array $results = [];

}
