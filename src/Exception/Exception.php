<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class Exception extends \Exception
{
    public const UNKNOWN_ERROR = 9999;

    /*
     * 关于参数.
     */
    public const PARAMS_SHORTCUT_ACTION_INVALID = 9210;

    public const PARAMS_METHOD_NOT_SUPPORTED = 9211;

    public const PARAMS_WECHAT_PAPAY_TYPE_NOT_SUPPORTED = 9212;

    public const PARAMS_WECHAT_URL_MISSING = 9213;

    public const PARAMS_WECHAT_BODY_MISSING = 9214;

    public const PARAMS_WECHAT_SERIAL_NOT_FOUND = 9215;

    public const PARAMS_UNIPAY_URL_MISSING = 9216;

    public const PARAMS_UNIPAY_BODY_MISSING = 9217;

    public const PARAMS_NECESSARY_PARAMS_MISSING = 9218;

    public const PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE = 9219;

    public const PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE = 9220;

    public const PARAMS_CALLBACK_REQUEST_INVALID = 9221;

    public const PARAMS_DOUYIN_URL_MISSING = 9222;

    /**
     * 关于响应.
     */
    public const RESPONSE_CODE_WRONG = 9304;

    public const RESPONSE_MISSING_NECESSARY_PARAMS = 9305;

    public const RESPONSE_BUSINESS_CODE_WRONG = 9306;

    /*
     * 关于配置.
     */
    public const CONFIG_ALIPAY_INVALID = 9401;

    public const CONFIG_WECHAT_INVALID = 9402;

    public const CONFIG_UNIPAY_INVALID = 9403;

    public const CONFIG_JSB_INVALID = 9404;

    public const CONFIG_DOUYIN_INVALID = 9405;

    /**
     * 关于签名.
     */
    public const SIGN_ERROR = 9500;

    public const SIGN_EMPTY = 9501;

    /**
     * 关于加解密.
     */
    public const DECRYPT_ERROR = 9600;

    public const DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID = 9601;

    public const DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID = 9602;

    public const DECRYPT_WECHAT_DECRYPTED_METHOD_INVALID = 9603;

    public const DECRYPT_WECHAT_ENCRYPTED_CONTENTS_INVALID = 9604;

    public mixed $extra;

    public function __construct(string $message = '未知异常', int $code = self::UNKNOWN_ERROR, mixed $extra = null, ?Throwable $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }
}
