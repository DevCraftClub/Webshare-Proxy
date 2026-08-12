<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ProxyStatus extends AbstractReflection {

	public ?string $state                 = NULL;
	public array   $countries             = [];
	public array   $unallocated_countries = [];
	public ?string $username              = NULL;
	public ?string $password              = NULL;
	public ?bool   $is_proxy_used         = NULL;

}
