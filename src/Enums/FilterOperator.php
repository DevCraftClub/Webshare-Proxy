<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Enums;

/**
 * WebShare API filter operators for list endpoints.
 *
 * @see https://apidocs.webshare.io/#filtering
 */
enum FilterOperator: string {
	case EQUAL    = '';
	case IN       = '__in';
	case GT       = '__gt';
	case LT       = '__lt';
	case CONTAINS = '__contains';
}
