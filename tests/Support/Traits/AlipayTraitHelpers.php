<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Enum\PayMode;

/**
 * Alipay Trait 测试 Helper。
 *
 * 构造最小可用 AlipayConfig 并解析 Authorization 头中的待签名串与签名值。
 */
trait AlipayTraitHelpers
{
    protected function getAlipayConfig(PayMode|int $mode = PayMode::NORMAL): AlipayConfig
    {
        return new AlipayConfig([
            'app_id' => 'app_id',
            'app_secret_cert' => 'app_secret_cert',
            'app_public_cert_path' => 'app_public_cert_path',
            'alipay_public_cert_path' => 'alipay_public_cert_path',
            'alipay_root_cert_path' => 'alipay_root_cert_path',
            'mode' => $mode,
        ], 'default');
    }

    private static function getAuthString(string $authorization): string
    {
        $rest = substr($authorization, strlen('ALIPAY-SHA256withRSA '));

        return substr($rest, 0, (int) strrpos($rest, ',sign='));
    }

    private static function getSign(string $authorization): string
    {
        return substr($authorization, (int) strrpos($authorization, ',sign=') + strlen(',sign='));
    }
}
