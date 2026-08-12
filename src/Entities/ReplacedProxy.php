<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Attributes\Range;
use Devcraft\Attributes\Filter;
use Devcraft\Abstracts\AbstractReflection;

class ReplacedProxy extends AbstractReflection {

	#[Filter(FILTER_VALIDATE_INT)]
	public ?int    $id                         = NULL;
	public ?string $reason                     = NULL;
	public ?string $proxy                      = NULL;
	#[Filter(FILTER_VALIDATE_INT)]
	#[Range(1, 65535)]
	public ?int    $proxy_port                 = NULL;
	public ?string $proxy_country_code         = NULL;
	public ?string $replaced_with              = NULL;
	#[Filter(FILTER_VALIDATE_INT)]
	#[Range(1, 65535)]
	public ?int    $replaced_with_port         = NULL;
	public ?string $replaced_with_country_code = NULL;
	public ?string $created_at                 = NULL;

}