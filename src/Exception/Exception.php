<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class Exception extends \Exception
{
    public const UNKNOWN_ERROR = 9999;

    /**
     * about container di.
     */
    public const CONTAINER_ERROR = 1000;

    public const NOT_FOUND_CONTAINER = 1001;

    public const CONTAINER_DEPENDENCY_ERROR = 1002;

    public const CONTAINER_NOT_FOUND_ENTRY = 1003;

    /**
     * about service.
     */
    public const SERVICE_EXCEPTION = 2000;

    public const SERVICE_NOT_FOUND_EXCEPTION = 2001;

    /**
     * raw.
     *
     * @var array
     */
    public $extra = [];

    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Unknown Error', array $extra = [], int $code = self::UNKNOWN_ERROR, Throwable $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }
}
