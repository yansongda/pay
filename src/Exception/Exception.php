<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class Exception extends \Exception
{
    public const UNKNOWN_ERROR = 9999;

    /**
     * 关于容器.
     */
    public const CONTAINER_ERROR = 1000;

    public const CONTAINER_NOT_FOUND = 1001;

    public const CONTAINER_DEPENDENCY_ERROR = 1002;

    public const CONTAINER_NOT_FOUND_ENTRY = 1003;

    /**
     * 关于服务.
     */
    public const SERVICE_ERROR = 2000;

    public const SERVICE_NOT_FOUND_ERROR = 2001;

    /*
     * 关于配置.
     */
    public const CONFIG_ERROR = 3000;

    public const INVALID_PACKER = 3001;

    public const ALIPAY_CONFIG_ERROR = 3002;

    /*
     * 关于参数.
     */
    public const PARAMS_ERROR = 4000;

    public const SHORTCUT_NOT_FOUND = 4001;

    public const PLUGIN_ERROR = 4002;

    /**
     * 关于api.
     */
    public const RESPONSE_ERROR = 5000;

    public const REQUEST_RESPONSE_ERROR = 5001;

    public const UNPACK_RESPONSE_ERROR = 5002;

    public const INVALID_RESPONSE_SIGN = 5003;

    /**
     * raw.
     *
     * @var array
     */
    public $extra = [];

    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Unknown Error', int $code = self::UNKNOWN_ERROR, array $extra = [], Throwable $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }
}
