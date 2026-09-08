<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadSignaturePluginTest extends TestCase
{
    protected AddPayloadSignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadSignaturePlugin();
    }

    public function testNormal(): void
    {
        $body = '{"out_trade_no":"yansongda","total_amount":"0.01"}';
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_body' => $body,
                'out_trade_no' => 'yansongda',
                'total_amount' => '0.01',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $payload = $result->getPayload()->all();
        $authorization = $payload['_authorization'];

        // Authorization 格式: `ALIPAY-SHA256withRSA <authString>,sign=<base64>`
        [$scheme, $rest] = explode(' ', $authorization, 2);
        [$authString, $sign] = explode(',sign=', $rest, 2);
        self::assertEquals('ALIPAY-SHA256withRSA', $scheme);

        // authString: 顺序 app_id → app_cert_sn → nonce → timestamp(13 位毫秒)
        self::assertMatchesRegularExpression(
            '/^app_id=alipay_v3_test_app_id,app_cert_sn=[0-9a-f]+,nonce=[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12},timestamp=\d{13}$/',
            $authString
        );

        // 重建 5 行组串(body 非空 + appAuthToken 缺省第 5 行整行缺省),签名可被支付宝公钥证书验证
        $content = $authString."\n"
            .'POST'."\n"
            .'/v3/alipay/trade/pay'."\n"
            .$body."\n";
        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'));
        self::assertEquals(1, openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256));
    }

    public function testNormalWithoutBody(): void
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3'])
            ->setPayload(new Collection([
                '_method' => 'GET',
                '_url' => '/v3/alipay/trade/query?out_trade_no=yansongda',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $authorization = $result->getPayload()->get('_authorization');
        [, $rest] = explode(' ', $authorization, 2);
        [$authString, $sign] = explode(',sign=', $rest, 2);

        // body 为空时保留空行,path 行含 query
        $content = $authString."\n"
            .'GET'."\n"
            .'/v3/alipay/trade/query?out_trade_no=yansongda'."\n"
            ."\n";
        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'));
        self::assertEquals(1, openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256));
    }

    public function testAppAuthTokenOverride(): void
    {
        $body = '{"out_trade_no":"yansongda"}';
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3', '_app_auth_token' => 'override_token'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_body' => $body,
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        // params `_app_auth_token` 优先,同时 merge 进 payload 供 AddRadarPlugin 输出 header
        self::assertEquals('override_token', $result->getPayload()->get('_app_auth_token'));

        // 组串含第 5 行 appAuthToken
        $authorization = $result->getPayload()->get('_authorization');
        [, $rest] = explode(' ', $authorization, 2);
        [$authString, $sign] = explode(',sign=', $rest, 2);
        $content = $authString."\n"
            .'POST'."\n"
            .'/v3/alipay/trade/pay'."\n"
            .$body."\n"
            .'override_token'."\n";
        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'));
        self::assertEquals(1, openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256));
    }

    public function testAppAuthTokenFallback(): void
    {
        Pay::config([
            'alipay' => [
                'alipay-v3-fallback' => [
                    'app_id' => 'alipay_v3_test_app_id',
                    'app_secret_cert' => __DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem',
                    'app_public_cert_path' => __DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt',
                    'alipay_public_cert_path' => __DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt',
                    'app_auth_token' => 'config_fallback_token',
                ],
            ],
            '_force' => true,
        ]);

        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3-fallback'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_body' => '{"out_trade_no":"yansongda"}',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        // params 未传时回落 config `app_auth_token`
        self::assertEquals('config_fallback_token', $result->getPayload()->get('_app_auth_token'));
    }

    public function testWithoutAppAuthToken(): void
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3'])
            ->setPayload(new Collection([
                '_method' => 'POST',
                '_url' => '/v3/alipay/trade/pay',
                '_body' => '{"out_trade_no":"yansongda"}',
            ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertArrayNotHasKey('_app_auth_token', $result->getPayload()->all());
    }
}
