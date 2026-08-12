<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class SubUser extends AbstractReflection {

	public ?int    $id                        = NULL;
	public ?string $label                     = NULL;
	public mixed   $proxy_countries           = NULL;
	public ?float  $proxy_limit               = NULL;
	public ?int    $max_thread_count          = NULL;
	public array   $aggregate_stats           = [];
	public ?string $created_at                = NULL;
	public ?string $updated_at                = NULL;
	public ?string $bandwidth_use_start_date  = NULL;
	public ?string $bandwidth_use_end_date    = NULL;

}
