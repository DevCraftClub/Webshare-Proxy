<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Devcraft\Webshare\Exceptions\ApiException;
use Devcraft\Webshare\Exceptions\RateLimitException;

final class ExceptionTest extends TestCase {

	public function testFromValidationResponseParsesFieldErrors(): void {
		$ex = ApiException::fromValidationResponse(json_encode([
			'mode' => [
				['message' => 'This field is required.', 'code' => 'required'],
			],
		], JSON_THROW_ON_ERROR));

		$this->assertSame(400, $ex->getCode());
		$this->assertStringContainsString('mode: This field is required.', $ex->getMessage());
		$errors = $ex->getValidationErrors();
		$this->assertSame('required', $errors['mode'][0]['code']);
	}

	public function testFromValidationResponseInvalidJson(): void {
		$ex = ApiException::fromValidationResponse('not-json');
		$this->assertSame('Bad Request', $ex->getMessage());
		$this->assertSame([], $ex->getValidationErrors());
	}

	public function testRateLimitExceptionMessage(): void {
		$ex = new RateLimitException(45);
		$this->assertSame(429, $ex->getCode());
		$this->assertSame(45, $ex->retryAfterSeconds);
		$this->assertStringContainsString('45s', $ex->getMessage());
	}

}
