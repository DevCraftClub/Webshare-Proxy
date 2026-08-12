<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Attributes\ArrayOf;
use Devcraft\Abstracts\AbstractReflection;

final class ProxyStat extends AbstractReflection {

	public ?string $timestamp              = NULL;
	public ?bool   $is_projected           = NULL;
	public ?int    $bandwidth_projected    = NULL;
	public ?int    $bandwidth_total        = NULL;
	public ?int    $bandwidth_average      = NULL;
	public ?int    $requests_total         = NULL;
	public ?int    $requests_successful    = NULL;
	public ?int    $requests_failed        = NULL;
	/**
	 * @var list<ProxyStatErrorReason>
	 */
	#[ArrayOf(ProxyStatErrorReason::class)]
	public array   $error_reasons          = [];
	public array   $countries_used         = [];
	public ?int    $number_of_proxies_used = NULL;
	public array   $protocols_used         = [];
	public ?float  $average_concurrency    = NULL;
	public ?float  $average_rps            = NULL;
	public ?string $last_request_sent_at   = NULL;

}
