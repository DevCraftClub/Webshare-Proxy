<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Attributes\Filter;
use Devcraft\Abstracts\AbstractReflection;

final class Profile extends AbstractReflection {
	public ?int    $id                                       = NULL;
	#[Filter(FILTER_VALIDATE_EMAIL)]
	public ?string $email                                    = NULL;
	public ?string $first_name                               = NULL;
	public ?string $last_name                                = NULL;
	public ?string $last_login                               = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $is_vip_customer                          = NULL;
	public ?string $timezone                                 = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $subscribed_bandwidth_usage_notifications = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $subscribed_subscription_notifications    = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $subscribed_proxy_usage_statistics        = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $subscribed_usage_warnings                = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $subscribed_guides_and_tips               = NULL;
	#[Filter(FILTER_VALIDATE_BOOLEAN)]
	public ?bool   $subscribed_survey_emails                 = NULL;
	public ?string $tracking_id                              = NULL;
	public ?string $helpscout_beacon_signature               = NULL;
	public ?string $intercom_signature                       = NULL;
	public ?string $announce_kit_user_token                  = NULL;
	public ?string $created_at                               = NULL;
	public ?string $updated_at                               = NULL;
}