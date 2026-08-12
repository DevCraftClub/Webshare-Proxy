<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ProxyListStats extends AbstractReflection {

	public array $available_countries    = [];
	public array $ip_ranges_24           = [];
	public array $ip_ranges_16           = [];
	public array $ip_ranges_8            = [];
	public array $available_ip_ranges_24 = [];
	public array $available_ip_ranges_16 = [];
	public array $available_ip_ranges_8  = [];
	public array $asns                   = [];
	public array $available_asns         = [];

}
