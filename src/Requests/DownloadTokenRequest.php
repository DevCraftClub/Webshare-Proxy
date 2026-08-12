<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\DownloadToken;
use Devcraft\Webshare\Abstracts\AbstractRequest;

final class DownloadTokenRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('download_token', ['scope']);
	}

	public function get(string $scope): DownloadToken {
		return DownloadToken::fromArray(json_decode(
			$this->request(RequestMethod::POST, rtrim($scope, '/') . '/'),
			true,
		));
	}

	public function reset(string $scope): DownloadToken {
		return DownloadToken::fromArray(json_decode(
			$this->request(RequestMethod::POST, rtrim($scope, '/') . '/reset/'),
			true,
		));
	}

}
