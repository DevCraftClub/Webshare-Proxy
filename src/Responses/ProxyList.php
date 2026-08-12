<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\Proxy;

final class ProxyList extends PaginatedResponse {

	/**
	 * @var list<Proxy>
	 */
	#[ArrayOf(Proxy::class)]
	public array   $results  = [];

}