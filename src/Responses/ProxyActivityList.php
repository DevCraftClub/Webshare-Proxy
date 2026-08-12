<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\ProxyActivity;

final class ProxyActivityList extends PaginatedResponse {

	/**
	 * @var list<ProxyActivity>
	 */
	#[ArrayOf(ProxyActivity::class)]
	public array $results = [];

}
