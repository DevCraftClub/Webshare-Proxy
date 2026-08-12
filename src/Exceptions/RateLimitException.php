<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Exceptions;

class RateLimitException extends ApiException {

	public function __construct(
		public readonly int $retryAfterSeconds = 60,
		?\Throwable $previous = null,
	) {
		parent::__construct(
			"Rate limit exceeded. Retry after {$retryAfterSeconds}s",
			429,
			$previous,
		);
	}
}
