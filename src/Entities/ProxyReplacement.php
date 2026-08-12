<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Attributes\Filter;
use Devcraft\Abstracts\AbstractReflection;

class ProxyReplacement extends AbstractReflection {

	#[Filter(FILTER_VALIDATE_INT)]
	public ?int    $id                   = NULL;
	public ?array  $to_replace           = NULL;
	public ?array  $replace_with         = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $dry_run              = NULL;
	public ?string $state                = NULL;
	#[Filter(FILTER_VALIDATE_INT)]
	public ?int    $proxies_removed      = NULL;
	#[Filter(FILTER_VALIDATE_INT)]
	public ?int    $proxies_added        = NULL;
	public ?string $reason               = NULL;
	public ?string $error_code           = NULL;
	public ?string $error                = NULL;
	public ?string $created_at           = NULL;
	public ?string $dry_run_completed_at = NULL;
	public ?string $completed_at         = NULL;

}