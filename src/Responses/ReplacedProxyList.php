<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\ReplacedProxy;

final class ReplacedProxyList extends PaginatedResponse {

	/**
	 * @var list<ReplacedProxy>
	 */
	#[ArrayOf(ReplacedProxy::class)]
	public array $results = [];

}