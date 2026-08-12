<?php

declare(strict_types=1);

namespace Devcraft\Webshare\Enums;

enum RequestMethod: string {
	case GET    = 'GET';
	case POST   = 'POST';
	case PUT    = 'PUT';
	case DELETE = 'DELETE';
	case PATCH  = 'PATCH';
}
