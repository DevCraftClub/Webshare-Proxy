<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Requests;

use Devcraft\Webshare\Entities\Profile;
use Devcraft\Webshare\Enums\RequestMethod;
use Devcraft\Webshare\Entities\RequestBody;
use Devcraft\Webshare\Abstracts\AbstractRequest;
use Devcraft\Webshare\Entities\ProfilePreference;

final class ProfileRequest extends AbstractRequest {

	public function __construct() {
		parent::__construct('profile', []);
	}

	/**
	 * @throws \JsonException
	 */
	public function retrieve(): Profile {
		return Profile::fromArray(json_decode($this->request(), true));
	}

	public function update(Profile $profile): Profile {
		$body = new RequestBody();
		foreach(array_filter($profile->toArray(), static fn($v) => $v !== NULL) as $key => $value) {
			$body->withPostData($key, $value);
		}

		return Profile::fromArray(json_decode($this->request(RequestMethod::PATCH, NULL, $body), true));
	}

	public function retrievePreferences(): ProfilePreference {
		return ProfilePreference::fromArray(json_decode($this->request(RequestMethod::GET, 'preferences'), true));
	}

	public function updatePreferences(ProfilePreference $preferences): ProfilePreference {
		$body = new RequestBody();
		foreach(array_filter($preferences->toArray(), static fn($v) => $v !== NULL) as $key => $value) {
			$body->withPostData($key, $value);
		}

		return ProfilePreference::fromArray(json_decode($this->request(RequestMethod::PATCH, 'preferences', $body), true));
	}

}