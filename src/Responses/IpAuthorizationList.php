<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\IpAuthorization;

final class IpAuthorizationList extends PaginatedResponse {

	/**
	 * @var list<IpAuthorization>
	 */
	#[ArrayOf(IpAuthorization::class)]
	public array $results = [];

}
