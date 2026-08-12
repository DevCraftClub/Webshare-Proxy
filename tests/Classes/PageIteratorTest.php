<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Tests\Classes;

use PHPUnit\Framework\TestCase;
use Devcraft\Webshare\Classes\PageIterator;
use Devcraft\Webshare\Exceptions\ApiException;
use Devcraft\Webshare\Responses\PaginatedResponse;

final class PageIteratorTest extends TestCase {

	public function testStopsWhenNextIsNull(): void {
		$calls = 0;
		$items = iterator_to_array(PageIterator::items(function() use (&$calls) {
			$calls++;
			$page = new PaginatedResponse();
			$page->results = ['a', 'b'];
			$page->next = NULL;

			return $page;
		}, 10));

		$this->assertSame(['a', 'b'], $items);
		$this->assertSame(1, $calls);
	}

	public function testThrowsWhenMaxPagesExceeded(): void {
		$this->expectException(ApiException::class);
		$this->expectExceptionMessage('PageIterator exceeded max pages (2)');

		iterator_to_array(PageIterator::items(function(int $i) {
			$page = new PaginatedResponse();
			$page->results = [$i];
			$page->next = 'https://example.com/next';

			return $page;
		}, 2));
	}

	public function testYieldsAcrossPagesUntilNextNull(): void {
		$items = iterator_to_array(PageIterator::items(function(int $i) {
			$page = new PaginatedResponse();
			$page->results = ["p{$i}"];
			$page->next = $i < 1 ? 'https://example.com/next' : NULL;

			return $page;
		}, 10));

		$this->assertSame(['p0', 'p1'], $items);
	}

}
