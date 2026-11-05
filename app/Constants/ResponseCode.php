<?php

declare(strict_types=1);

namespace App\Constants;

class ResponseCode
{
    // Success Response Codes
    public const SUCCESS = 200;

    public const CREATED = 201;

    public const ACCEPTED = 202;

    public const NO_CONTENT = 204;

    // Client Error Response Codes
    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    public const METHOD_NOT_ALLOWED = 405;

    public const NOT_ACCEPTABLE = 406;

    public const REQUEST_TIMEOUT = 408;

    public const CONFLICT = 409;

    public const GONE = 410;

    public const UNPROCESSABLE_ENTITY = 422;

    public const TOO_MANY_REQUESTS = 429;

    // Server Error Response Codes
    public const INTERNAL_SERVER_ERROR = 500;

    public const NOT_IMPLEMENTED = 501;

    public const BAD_GATEWAY = 502;

    public const SERVICE_UNAVAILABLE = 503;

    public const GATEWAY_TIMEOUT = 504;
}
