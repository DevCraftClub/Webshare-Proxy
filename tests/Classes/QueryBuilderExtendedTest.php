<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Classes;

use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Enums\FilterOperator;
use Devcraft\Webshare\Classes\QueryBuilder;
use Devcraft\Webshare\Tests\TestCaseWithApp;
use Devcraft\Webshare\Exceptions\ApiException;

final class QueryBuilderExtendedTest extends TestCaseWithApp {

	public function testOrderSearchAndOperators(): void {
		$query = (new QueryBuilder())
			->withOptionalFields(['status', 'amount', 'city', 'ordering', 'search'])
			->withFilter('status', ['a', 'b'], FilterOperator::IN)
			->withFilter('amount', 10, FilterOperator::GT)
			->withFilter('city', 'New', FilterOperator::CONTAINS)
			->withOrderBy('-created_at', 'status')
			->withSearch('Free')
			->withPage(2)
			->withPageSize(50);

		$built = $query->build();
		$this->assertStringContainsString('status__in=a%2Cb', $built);
		$this->assertStringContainsString('amount__gt=10', $built);
		$this->assertStringContainsString('city__contains=New', $built);
		$this->assertStringContainsString('ordering=-created_at%2Cstatus', $built);
		$this->assertStringContainsString('search=Free', $built);
		$this->assertStringContainsString('page=2', $built);
		$this->assertStringContainsString('page_size=50', $built);
	}

	public function testUnknownFieldThrows(): void {
		$this->expectException(ApiException::class);
		(new QueryBuilder())
			->withOptionalFields(['status'])
			->withFilter('nope', 1);
	}

}
