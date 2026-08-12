<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ProxyActivity extends AbstractReflection {

	public ?string $timestamp                = NULL;
	public ?string $protocol                 = NULL;
	public ?float  $request_duration         = NULL;
	public ?float  $handshake_duration       = NULL;
	public ?float  $tunnel_duration          = NULL;
	public ?string $error_reason             = NULL;
	public ?string $error_reason_how_to_fix = NULL;
	public ?string $auth_username            = NULL;
	public ?string $proxy_address            = NULL;
	public ?int    $bytes                    = NULL;
	public ?string $client_address           = NULL;
	public ?string $ip_address               = NULL;
	public ?string $hostname                 = NULL;
	public ?string $domain                   = NULL;
	public ?int    $port                     = NULL;
	public ?int    $proxy_port               = NULL;
	public ?string $listen_address           = NULL;
	public ?int    $listen_port              = NULL;

}
