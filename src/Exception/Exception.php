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

    public const CONTAINER_NOT_FOUND_ENTRY = 1002;

    /**
     * 关于容器的服务.
     */
    public const SERVICE_ERROR = 2000;

    public const SERVICE_NOT_FOUND = 2001;

    /*
     * 关于配置.
     */
    public const CONFIG_ERROR = 3000;

    public const DIRECTION_INVALID = 3001;

    public const ALIPAY_CONFIG_INVALID = 3002;

    public const LOGGER_CONFIG_INVALID = 3003;

    public const HTTP_CLIENT_INVALID = 3004;

    public const EVENT_CONFIG_INVALID = 3005;

    public const WECHAT_CONFIG_INVALID = 3006;

    public const UNIPAY_CONFIG_INVALID = 3007;

    public const PACKER_INVALID = 3008;

    /*
     * 关于参数.
     */
    public const PARAMS_ERROR = 4000;

    public const SHORTCUT_NOT_FOUND = 4001;

    public const PLUGIN_INCOMPATIBLE = 4002;

    public const SHORTCUT_MULTI_ACTION_INVALID = 4003;

    public const METHOD_NOT_SUPPORTED = 4004;

    public const REQUEST_EMPTY = 4005;

    public const NECESSARY_PARAMS_MISSING = 4006;

    public const CURRENT_PLUGIN_ONLY_SUPPORT_SERVICE_MODE = 4007;

    public const WECHAT_SERIAL_NOT_FOUND = 4008;

    /**
     * 关于api.
     */
    public const RESPONSE_ERROR = 5000;

    public const REQUEST_RESPONSE_ERROR = 5001;

    public const UNPACK_RESPONSE_ERROR = 5002;

    public const SIGN_INVALID = 5003;

    public const RESPONSE_CODE_WRONG = 5004;

    public const RESPONSE_MISSING_NECESSARY_PARAMS = 5005;

    public const RESPONSE_EMPTY = 5006;

    public const CIPHERTEXT_PARAMS_INVALID = 5007;

    public const REQUEST_ENCRYPTED_DATA_INVALID = 5008;

    public const REQUEST_ENCRYPTED_METHOD_INVALID = 5009;

    public mixed $extra;

    public function __construct(string $message = 'Unknown Error', int $code = self::UNKNOWN_ERROR, mixed $extra = null, Throwable $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }
}
