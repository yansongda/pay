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

    /*
     * 关于配置.
     */
    public const CONFIG_ALIPAY_INVALID = 9401;

    public const CONFIG_WECHAT_INVALID = 9402;

    public const CONFIG_UNIPAY_INVALID = 9403;







    public const PARAMS_NECESSARY_PARAMS_MISSING = 4005;

    public const PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE = 4006;

    public const PARAMS_WECHAT_SERIAL_NOT_FOUND = 4007;

    public const PARAMS_CALLBACK_REQUEST_INVALID = 4008;

    public const PARAMS_WECHAT_URL_MISSING = 4009;

    public const PARAMS_WECHAT_BODY_MISSING = 4010;



    public const PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE = 4012;

    public const PARAMS_UNIPAY_URL_MISSING = 4013;

    public const PARAMS_UNIPAY_BODY_MISSING = 4014;

    /**
     * 关于响应.
     */

    public const RESPONSE_CODE_WRONG = 5003;

    public const RESPONSE_MISSING_NECESSARY_PARAMS = 5004;

    public const RESPONSE_EMPTY = 5005;

    /**
     * 关于签名.
     */
    public const SIGN_ERROR = 6000;

    public const SIGN_EMPTY = 6001;

    /**
     * 关于加解密.
     */
    public const DECRYPT_ERROR = 7000;

    public const DECRYPT_WECHAT_CIPHERTEXT_PARAMS_INVALID = 7001;

    public const DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID = 7002;

    public const DECRYPT_WECHAT_DECRYPTED_METHOD_INVALID = 7003;

    public const DECRYPT_WECHAT_ENCRYPTED_CONTENTS_INVALID = 7004;

    public mixed $extra;

    public function __construct(string $message = '未知异常', int $code = self::UNKNOWN_ERROR, mixed $extra = null, ?Throwable $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }
}
