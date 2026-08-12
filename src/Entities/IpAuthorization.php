<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class IpAuthorization extends AbstractReflection {

	public ?int    $id           = NULL;
	public ?string $ip_address   = NULL;
	public ?string $created_at   = NULL;
	public ?string $last_used_at = NULL;

}
