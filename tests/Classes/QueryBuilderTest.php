<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Classes;

use PHPUnit\Framework\TestCase;
use Devcraft\Webshare\Classes\QueryBuilder;
use Devcraft\Webshare\Exceptions\ApiException;

final class QueryBuilderTest extends TestCase {

	public function testBuildWithEmptyRequiredFieldsSucceeds(): void {
		$query = (new QueryBuilder())->withOptionalFields(['ordering']);
		$this->assertSame('', $query->build());
	}

	public function testBuildRequiresAllRequiredFields(): void {
		$query = (new QueryBuilder())
			->withOptionalFields(['mode', 'plan_id'])
			->withRequiredFields(['mode']);

		$this->expectException(ApiException::class);
		$this->expectExceptionMessage('Required fields are not set: mode');
		$query->build();
	}

	public function testBuildPassesWhenRequiredFieldPresent(): void {
		$query = (new QueryBuilder())
			->withOptionalFields(['mode'])
			->withRequiredFields(['mode'])
			->withFilter('mode', 'direct');

		$this->assertStringContainsString('mode=direct', $query->build());
	}

}
