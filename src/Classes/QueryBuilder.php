<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Classes;

use Lombok\Getter;
use Lombok\Setter;
use Devcraft\Webshare\Enums\FilterOperator;
use Devcraft\Webshare\Exceptions\ApiException;
use Devcraft\Webshare\Abstracts\AbstractBasicClass;

#[Getter, Setter]
class QueryBuilder extends AbstractBasicClass {

	private ?int    $pageSize       = NULL;
	private ?int    $page           = NULL;
	private ?string $startingAfter  = NULL;
	private array   $filters        = [];
	private array   $ordering       = [];
	private ?string $search         = NULL;
	private array   $requiredFields = [];
	private array   $optionalFields = [];

	public function withPageSize(int $pageSize): self {
		$this->pageSize = $pageSize;

		return $this;
	}

	public function withPage(int $page): self {
		$this->page = $page;

		return $this;
	}

	public function withStartingAfter(string $startingAfter): self {
		$this->startingAfter = $startingAfter;

		return $this;
	}

	public function withRequiredField(string $field): self {
		$this->requiredFields[] = $field;

		return $this;
	}

	public function withOptionalField(string $field): self {
		$this->optionalFields[] = $field;

		return $this;
	}

	public function withRequiredFields(array $fields): self {
		$this->requiredFields = array_merge($this->requiredFields, $fields);

		return $this;
	}

	public function withOptionalFields(array $fields): self {
		$this->optionalFields = array_merge($this->optionalFields, $fields);

		return $this;
	}

	/**
	 * Add a filter parameter using WebShare API filter syntax.
	 *
	 * @see https://apidocs.webshare.io/#filtering
	 */
	public function withFilter(string $field, mixed $value, FilterOperator $operator = FilterOperator::EQUAL): self {
		$this->checkForPossibleField($field);

		$key                 = $field . $operator->value;
		$this->filters[$key] = is_array($value)? implode(',', $value) : $value;

		return $this;
	}

	/**
	 * Set ordering for list endpoints. Prefix with `-` for descending.
	 *
	 * @see https://apidocs.webshare.io/#ordering
	 *
	 * @param   string  ...$fields  e.g. 'status', '-created_at'
	 */
	public function withOrderBy(string ...$fields): self {
		$this->ordering = array_merge($this->ordering, $fields);

		return $this;
	}

	/**
	 * Set text search parameter.
	 *
	 * @see https://apidocs.webshare.io/#search
	 */
	public function withSearch(string $term): self {
		$this->search = $term;

		return $this;
	}

	public function build(): string {
		$params = array_filter([
			'page_size'      => $this->pageSize,
			'page'           => $this->page,
			'starting_after' => $this->startingAfter,
			'ordering'       => $this->ordering? implode(',', $this->ordering) : NULL,
			'search'         => $this->search,
			...$this->filters,
		], static fn($v) => $v !== NULL);

		$this->checkRequiredFields($params);

		return http_build_query($params);
	}

	public function getFields(): array {
		return array_merge($this->getRequiredFields(), $this->getOptionalFields());
	}

	private function checkForPossibleField(string $field): void {
		if (!in_array($field, $this->getFields())) {
			throw new ApiException("Field '$field' is not allowed.");
		}
	}

	private function checkRequiredFields(array $parameters): void {
		if($this->requiredFields === []) {
			return;
		}

		$missing = array_diff($this->requiredFields, array_keys($parameters));
		if($missing !== []) {
			throw new ApiException('Required fields are not set: ' . implode(', ', $missing));
		}
	}
}
