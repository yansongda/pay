<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use function Yansongda\Artful\filter_params;

/**
 * 支付宝异步通知 Helper。
 *
 * 生成模拟支付宝异步通知的 form 参数（用测试私钥按 V2 参数格式签名，密钥与 alipay-v3 测试租户支付宝公钥证书同属一对）.
 */
trait AlipayCallbackHelpers
{
    private function makeSignedForm(): array
    {
        $form = [
            'app_id' => 'alipay_v3_test_app_id',
            'trade_no' => '2023122122001499160501589436',
            'out_trade_no' => '1703147160',
            'total_amount' => '0.01',
            'trade_status' => 'TRADE_SUCCESS',
            'sign_type' => 'RSA2',
        ];

        $value = filter_params($form, fn ($k, $v) => '' !== $v && 'sign' != $k && 'sign_type' != $k)->sortKeys()->toQueryString();

        openssl_sign($value, $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipay-v3/app_secret_test.pem'))), OPENSSL_ALGO_SHA256);

        $form['sign'] = base64_encode($sign);

        return $form;
    }
}
