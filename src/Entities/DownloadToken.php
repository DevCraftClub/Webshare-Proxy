<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class DownloadToken extends AbstractReflection {

	public ?int    $id        = NULL;
	public ?string $key       = NULL;
	public ?string $scope     = NULL;
	public ?string $expire_at = NULL;

}
