<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use function Yansongda\Artful\filter_params;

/**
 * 支付宝应用网关（verifygw）回调 Helper。
 *
 * 按 verifygw 组串规则签名：剔除 `sign`、保留 `sign_type`，密钥与 `alipay-v3` 测试租户
 * 支付宝公钥证书同属一对.
 *
 * 生成模拟支付宝应用网关请求的 form 参数（用测试私钥按 verifygw 组串规则签名：仅剔除 `sign`，保留 `sign_type`，密钥与 alipay-v3 测试租户支付宝公钥证书同属一对）.
 */
trait AlipayGatewayCallbackHelpers
{
    /**
     * 生成模拟支付宝应用网关请求的 form 参数。
     *
     * @param array<string, string> $overrides 覆盖业务参数（在签名前应用，用于篡改组串场景）
     * @param array<string, string> $extra     追加不参与组串的 `_` 前缀参数（验证 `_` 前缀不污染组串）
     *
     * @return array<string, string>
     */
    private function makeSignedForm(array $overrides = [], array $extra = ['_config' => 'alipay-v3']): array
    {
        $form = array_merge([
            'service' => 'alipay.service.check',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'biz_content' => '<XML><AppId>alipay_v3_test_app_id</AppId><MsgType>event</MsgType><EventType>verifygw</EventType></XML>',
        ], $overrides);

        $value = filter_params(array_merge($form, $extra), fn ($k, $v) => '' !== $v && 'sign' != $k)
            ->sortKeys()
            ->toQueryString();

        openssl_sign($value, $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipay-v3/app_secret_test.pem'))), OPENSSL_ALGO_SHA256);

        $form['sign'] = base64_encode($sign);

        return array_merge($form, $extra);
    }

    /**
     * 从 fixture 应用公钥证书提取应用公钥主体（剥离 PEM 头尾与换行的裸公钥串）.
     */
    private function appPublicKey(): string
    {
        $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('alipay-v3/alipay_public_cert_test.crt')));
        expect($publicKey)->not->toBeFalse();

        $details = openssl_pkey_get_details($publicKey);
        expect($details)->toBeArray();
        expect($details)->toHaveKey('key');

        return str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\r", "\n"], '', $details['key']);
    }
}
