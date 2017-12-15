<?php

namespace Yansongda\Pay\Gateways\Alipay;

class Support
{
    /**
     * Generate sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function generateSign(): string
    {
        if (is_null($this->user_config->get('private_key'))) {
            throw new InvalidArgumentException('Missing Config -- [private_key]');
        }

        $res = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($this->user_config->get('private_key'), 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($this->getSignContent($this->config), $sign, $res, OPENSSL_ALGO_SHA256);

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
    protected function getSignContent(array $toBeSigned, $verify = false): string
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