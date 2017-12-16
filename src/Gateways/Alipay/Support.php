<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Supports\Str;

class Support
{
    /**
     * Generate sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    public static function generateSign($parmas, $privateKey = null): string
    {
        if (is_null($privateKey)) {
            throw new InvalidConfigException('Missing Config -- [private_key]');
        }

        if (Str::endsWith($privateKey, '.pem')) {
            $privateKey = openssl_pkey_get_private($privateKey);
        } else {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($privateKey, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }

        openssl_sign(self::getSignContent($parmas), $sign, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * Get signContent that is to be signed.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $toBeSigned
     * @param bool  $verify
     *
     * @return string
     */
    public static function getSignContent(array $toBeSigned, $verify = false): string
    {
        ksort($toBeSigned);

        $stringToBeSigned = '';
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset($k, $v);

        return $stringToBeSigned;
    }
}