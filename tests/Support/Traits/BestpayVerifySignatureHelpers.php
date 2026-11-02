<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Pay\Config\BestpayConfig;

/**
 * 翼支付响应验签 Helper。
 */
trait BestpayVerifySignatureHelpers
{
    private function makeConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => cert_path('bestpay/bestpay.p12'),
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => cert_path('bestpay/bestpay.cer'),
        ]);
    }
}
