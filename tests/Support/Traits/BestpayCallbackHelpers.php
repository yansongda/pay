<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Pay\Config\BestpayConfig;

/**
 * 翼支付回调 Helper。
 *
 * 提供 default / second 两个租户配置（证书与 merchant_no 不同），用于多租户回调验签。
 */
trait BestpayCallbackHelpers
{
    private function defaultConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => cert_path('bestpay/bestpay.p12'),
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => cert_path('bestpay/bestpay.cer'),
        ]);
    }

    private function secondConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245779',
            'institution_code' => '3178033925245779',
            'mch_secret_cert_path' => cert_path('bestpay/second.p12'),
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => cert_path('bestpay/second.cer'),
        ]);
    }
}
