<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Exceptions;

class ApiException extends \RuntimeException {

	private array $validationErrors = [];

	public function __construct(string $message, int $code = 0, ?\Throwable $previous = null, array $validationErrors = []) {
		$this->validationErrors = $validationErrors;
		parent::__construct($message, $code, $previous);
	}

	public function getValidationErrors(): array {
		return $this->validationErrors;
	}

	/**
	 * Parse the WebShare API 400 response body into structured validation errors.
	 *
	 * @return array<string, list<array{message: string, code: string}>>
	 */
	public static function fromValidationResponse(string $body, int $statusCode = 400): self {
		$decoded = json_decode($body, true);
		if (!is_array($decoded)) {
			return new self('Bad Request', $statusCode);
		}

		$errors = [];
		$messages = [];
		foreach ($decoded as $field => $fieldErrors) {
			if (!is_array($fieldErrors)) {
				continue;
			}
			foreach ($fieldErrors as $error) {
				$errors[$field][] = [
					'message' => $error['message'] ?? 'Unknown error',
					'code' => $error['code'] ?? 'unknown',
				];
				$messages[] = "$field: " . ($error['message'] ?? 'Unknown error');
			}
		}

		return new self(
			$messages ? implode('; ', $messages) : 'Bad Request',
			$statusCode,
			null,
			$errors,
		);
	}
}
