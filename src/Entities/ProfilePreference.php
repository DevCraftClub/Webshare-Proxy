<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

final class ProfilePreference extends AbstractReflection {
	public ?int    $id                                             = NULL;
	public ?string $customer_satisfaction_survey_last_dismissed_at = NULL;
	public ?string $customer_satisfaction_survey_last_completed_at = NULL;
	public ?string $onboarding_activity_page_viewed_at             = NULL;
	public ?string $created_at                                     = NULL;
	public ?string $updated_at                                     = NULL;
}