<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Entities;

use Devcraft\Abstracts\AbstractReflection;

class RequestBody extends AbstractReflection {

	private const CONTENT_TYPE_JSON = 'application/json';

	private const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

	/**
	 * @var array<string, mixed>
	 */
	public array $header = [];

	public ?string $body = NULL;

	/**
	 * @var array<string, mixed>
	 */
	public array $postData = [];

	/**
	 * @var array<string, mixed>
	 */
	public array $formData = [];

	/**
	 * @var list<array{name: string, contents: mixed}>
	 */
	public array $multipart = [];

	public function withPostData(string $key, mixed $value): self {
		$this->guardBodyTypeConflict('postData');
		$this->postData[$key] = $value;

		return $this;
	}

	public function withHeader(string $key, mixed $value): self {
		$this->header[$key] = $value;

		return $this;
	}

	public function withFormData(string $key, mixed $value): self {
		$this->guardBodyTypeConflict('formData');
		$this->formData[$key] = $value;

		return $this;
	}

	public function withMultipart(string $key, mixed $value): self {
		$this->guardBodyTypeConflict('multipart');
		$this->multipart[] = [
			'name'     => $key,
			'contents' => $value,
		];

		return $this;
	}

	/**
	 * @throws \JsonException
	 */
	public function build(): self {
		$this->prepare();

		return $this;
	}

	private function guardBodyTypeConflict(string $incoming): void {
		$active = array_filter([
			'postData'  => !empty($this->postData),
			'formData'  => !empty($this->formData),
			'multipart' => !empty($this->multipart),
		]);

		unset($active[$incoming]);

		if($active !== []) {
			throw new \LogicException(sprintf(
				'Cannot use "%s": request body already uses "%s". Only one body type is allowed per request.',
				$incoming,
				implode('", "', array_keys($active)),
			));
		}
	}

	/**
	 * @throws \JsonException
	 */
	private function prepare(): void {
		$this->header = array_filter($this->header, static fn($value) => $value !== NULL);

		if(!array_key_exists('Content-Type', $this->header) && !isset(array_change_key_case($this->header)['content-type'])) {
			$this->header['Content-Type'] = $this->resolveContentType();
		}

		if(!array_key_exists('Accept', $this->header) && !isset(array_change_key_case($this->header)['accept'])) {
			$this->header['Accept'] = self::CONTENT_TYPE_JSON;
		}

		$this->body = $this->buildBody();
	}

	private function resolveContentType(): string {
		return match (true) {
			$this->formData !== [] => self::CONTENT_TYPE_FORM,
			default                => self::CONTENT_TYPE_JSON,
		};
	}

	/**
	 * @throws \JsonException
	 */
	private function buildBody(): ?string {
		if($this->formData !== []) {
			$formData = array_filter($this->formData, static fn($value) => $value !== NULL);

			return http_build_query($formData);
		}

		if($this->multipart !== []) {
			return NULL;
		}

		if($this->postData === []) {
			return NULL;
		}

		$postData = array_filter($this->postData, static fn($value) => $value !== NULL);

		return json_encode($postData, JSON_THROW_ON_ERROR);
	}

}