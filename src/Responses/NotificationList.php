<?php

declare(strict_types=1);
namespace Devcraft\Webshare\Responses;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Webshare\Entities\Notification;

class NotificationList extends PaginatedResponse {
	/**
	 * @var list<Notification>
	 */
	#[ArrayOf(Notification::class)]
	public array $results = [];
}