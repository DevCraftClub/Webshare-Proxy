<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\ProxyReplacement;

final class ProxyReplacementList extends PaginatedResponse {

	/**
	 * @var list<ProxyReplacement>
	 */
	#[ArrayOf(ProxyReplacement::class)]
	public array $results = [];

}