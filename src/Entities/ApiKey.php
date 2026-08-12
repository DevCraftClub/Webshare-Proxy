<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ApiKey extends AbstractReflection {

	public ?int    $id         = NULL;
	public ?string $key        = NULL;
	public ?string $label      = NULL;
	public ?string $created_at = NULL;
	public ?string $updated_at = NULL;

}
