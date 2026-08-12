<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ProxyConfig extends AbstractReflection {

	public ?int    $id                                             = NULL;
	public ?string $state                                          = NULL;
	public array   $countries                                      = [];
	public array   $available_countries                            = [];
	public array   $unallocated_countries                          = [];
	public array   $ip_ranges_24                                   = [];
	public array   $ip_ranges_16                                   = [];
	public array   $ip_ranges_8                                    = [];
	public array   $available_ip_ranges_24                         = [];
	public array   $available_ip_ranges_16                         = [];
	public array   $available_ip_ranges_8                          = [];
	public array   $asns                                           = [];
	public array   $available_asns                                 = [];
	public ?string $username                                       = NULL;
	public ?string $password                                       = NULL;
	public ?int    $request_timeout                                = NULL;
	public ?int    $request_idle_timeout                           = NULL;
	public mixed   $ip_authorization_country_codes                 = NULL;
	public ?string $ip_authorization_city                          = NULL;
	public ?string $ip_authorization_asn                           = NULL;
	public ?bool   $auto_replace_invalid_proxies                   = NULL;
	public ?bool   $auto_replace_low_country_confidence_proxies    = NULL;
	public ?bool   $auto_replace_out_of_rotation_proxies           = NULL;
	public ?bool   $auto_replace_failed_site_check_proxies         = NULL;
	public ?string $proxy_list_download_token                      = NULL;
	public ?bool   $is_proxy_used                                  = NULL;
	public ?string $created_at                                     = NULL;
	public ?string $updated_at                                     = NULL;

}
