<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ProxyStatErrorReason extends AbstractReflection {

	public ?string $reason       = NULL;
	public ?string $type         = NULL;
	public ?string $how_to_fix = NULL;
	public ?int    $http_status  = NULL;
	public ?int    $count        = NULL;

}
