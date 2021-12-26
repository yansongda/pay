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
     * 关于容器的服务.
     */
    public const SERVICE_ERROR = 2000;

    public const SERVICE_NOT_FOUND_ERROR = 2001;

    /*
     * 关于配置.
     */
    public const CONFIG_ERROR = 3000;

    public const INVALID_PACKER = 3001;

    public const ALIPAY_CONFIG_ERROR = 3002;

    public const LOGGER_CONFIG_ERROR = 3003;

    public const HTTP_CLIENT_CONFIG_ERROR = 3004;

    public const EVENT_CONFIG_ERROR = 3005;

    public const WECHAT_CONFIG_ERROR = 3006;

    /*
     * 关于参数.
     */
    public const PARAMS_ERROR = 4000;

    public const SHORTCUT_NOT_FOUND = 4001;

    public const PLUGIN_ERROR = 4002;

    public const SHORTCUT_QUERY_TYPE_ERROR = 4003;

    public const METHOD_NOT_SUPPORTED = 4004;

    public const REQUEST_NULL_ERROR = 4005;

    public const MISSING_NECESSARY_PARAMS = 4006;

    public const NOT_IN_SERVICE_MODE = 4007;

    public const WECHAT_SERIAL_NO_NOT_FOUND = 4008;

    /**
     * 关于api.
     */
    public const RESPONSE_ERROR = 5000;

    public const REQUEST_RESPONSE_ERROR = 5001;

    public const UNPACK_RESPONSE_ERROR = 5002;

    public const INVALID_RESPONSE_SIGN = 5003;

    public const INVALID_RESPONSE_CODE = 5004;

    public const RESPONSE_MISSING_NECESSARY_PARAMS = 5005;

    public const RESPONSE_NONE = 5006;

    public const INVALID_CIPHERTEXT_PARAMS = 5007;

    public const INVALID_REQUEST_ENCRYPTED_DATA = 5008;

    public const INVALID_REQUEST_ENCRYPTED_METHOD = 5009;

    /**
     * raw.
     *
     * @var mixed
     */
    public $extra = null;

    /**
     * Bootstrap.
     *
     * @param mixed $extra
     */
    public function __construct(string $message = 'Unknown Error', int $code = self::UNKNOWN_ERROR, $extra = null, Throwable $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }
}
