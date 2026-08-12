<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\Notification;
use Devcraft\Webshare\Abstracts\AbstractRequest;
use Devcraft\Webshare\Responses\NotificationList;

class NotificationRequest extends AbstractRequest {
	public function __construct() {
		parent::__construct('notification', ['dismissed_at__isnull', 'ordering', 'type']);
	}

	public function list(): NotificationList {
		return NotificationList::fromArray(json_decode($this->request(), true));
	}

	public function retrieve(int $id): Notification {
		return Notification::fromArray(json_decode($this->request(additionalEndpoint: (string) $id), true));
	}

	public function dismiss(int $id): Notification {
		return Notification::fromArray(json_decode($this->request(RequestMethod::POST, "$id/dismiss/"), true));
	}

	public function restore(int $id): Notification {
		return Notification::fromArray(json_decode($this->request(RequestMethod::POST, "$id/restore/"), true));
	}
}