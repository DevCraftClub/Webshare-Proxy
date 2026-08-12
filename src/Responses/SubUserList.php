<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\SubUser;

final class SubUserList extends PaginatedResponse {

	/**
	 * @var list<SubUser>
	 */
	#[ArrayOf(SubUser::class)]
	public array $results = [];

}
