<?php

namespace Yansongda\Pay\Exception;

use Throwable;

class Exception extends \Exception
{
    const UNKNOWN_ERROR = 9999;

    /**
     * about container di.
     */
    const CONTAINER_ERROR = 1000;

    const NOT_FOUND_CONTAINER = 1001;

    const CONTAINER_DEPENDENCY_ERROR = 1002;

    /**
     * about service.
     */
    const SERVICE_EXCEPTION = 2000;

    const SERVICE_NOT_FOUND_EXCEPTION = 2001;

    /**
     * raw.
     *
     * @var array
     */
    public $extra = [];

    /**
     * Bootstrap.
     *
     * @param array|string $extra
     */
    public function __construct($message = 'Unknown Error', $extra = [], $code = self::UNKNOWN_ERROR, Throwable $previous = null)
    {
        $this->extra = is_array($extra) ? $extra : [$extra];

        parent::__construct($message, $code, $previous);
    }
}
