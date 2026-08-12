<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Attributes\Filter;
use Devcraft\Abstracts\AbstractReflection;

class Notification extends AbstractReflection {

	#[Filter(FILTER_VALIDATE_INT)]
	public ?int    $id             = NULL;
	public ?string $type           = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $is_dismissable = NULL;
	public ?array  $context        = NULL;
	public ?string $created_at     = NULL;
	public ?string $updated_at     = NULL;
	public ?string $dismissed_at   = NULL;
}