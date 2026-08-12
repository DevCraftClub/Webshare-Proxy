<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Attributes\Range;
use Devcraft\Attributes\Regex;
use Devcraft\Abstracts\AbstractReflection;

final class Proxy extends AbstractReflection {

	public ?string $id                      = NULL;
	public ?string $username                = NULL;
	public ?string $password                = NULL;
	public ?string $proxy_address           = NULL;
	#[Range(min: 1, max: 65535)]
	public ?int    $port                    = NULL;
	public ?bool   $valid                   = NULL;
	public ?string $last_verification       = NULL;
	#[Regex('/^[A-Z]{2}$/')]
	public ?string $country_code            = NULL;
	public ?string $city_name               = NULL;
	public ?string $asn_name                = NULL;
	public ?int    $asn_number              = NULL;
	public ?bool   $high_country_confidence = NULL;
	public ?string $created_at              = NULL;

	/**
	 * Generate a formatted connection string for this proxy.
	 *
	 * @param   bool         $backbone  Use backbone (p.webshare.io) instead of direct IP (requires username auth)
	 * @param   string|null  $session   Rotate session ID (e.g. 'rotate', '1234')
	 * @param   string|null  $city      Target city (e.g. 'los_angeles')
	 *
	 * @return string e.g. "http://user:pass@1.2.3.4:80" or "http://user-us-rotate:pass@p.webshare.io:80"
	 */
	public function getConnectionString(bool $backbone = false, ?string $session = NULL, ?string $city = NULL): string {
		if(!$this->username || !$this->password) {
			return sprintf("http://%s:%s", $this->proxy_address, $this->port);
		}

		if(!$backbone) {
			return sprintf("http://%s:%s@%s:%s", $this->username, $this->password, $this->proxy_address, $this->port);
		}

		$user = $this->username;
		if($this->country_code) {
			$user .= '-' . strtolower($this->country_code);
		}
		if($city !== NULL) {
			$user .= '-city_' . str_replace(' ', '_', strtolower($city));
		}
		if($session !== NULL) {
			$user .= '-' . $session;
		}

		return sprintf("http://%s:%s@p.webshare.io:80", $user, $this->password);
	}

}