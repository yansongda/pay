<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Virtual\AddPayloadSignaturePlugin;
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

    public function testAssemblyNormal()
    {
        $body = '{"openid":"oUpF8muMJAaName"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance',
            '_body' => $body,
            'access_token' => 'test_access_token',
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();
        $url = $resultPayload->get('_url');

        self::assertStringContainsString('access_token=test_access_token', $url);
        self::assertStringContainsString('pay_sig=', $url);
        self::assertStringNotContainsString('signature=', $url);

        // verify pay_sig value
        $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
        $expectedPaySig = hash_hmac('sha256', '/xpay/query_user_balance&'.$body, $config->getVirtualPay()->getAppKey());
        self::assertStringContainsString('pay_sig='.$expectedPaySig, $url);

        // paySig 可作为 payload 独立字段访问
        self::assertEquals($expectedPaySig, $resultPayload->get('paySig'));
        self::assertNull($resultPayload->get('signature'));
    }

    public function testAssemblyWithSessionKey()
    {
        $body = '{"openid":"oUpF8muMJAaName"}';
        $sessionKey = 'test_session_key_value';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance',
            '_body' => $body,
            'access_token' => 'test_access_token',
            '_session_key' => $sessionKey,
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();
        $url = $resultPayload->get('_url');

        self::assertStringContainsString('access_token=test_access_token', $url);
        self::assertStringContainsString('pay_sig=', $url);
        self::assertStringContainsString('signature=', $url);

        // verify signature value
        $expectedSignature = hash_hmac('sha256', $body, $sessionKey);
        self::assertStringContainsString('signature='.$expectedSignature, $url);

        // paySig 和 signature 可作为 payload 独立字段访问
        self::assertNotNull($resultPayload->get('paySig'));
        self::assertEquals($expectedSignature, $resultPayload->get('signature'));
    }

    public function testAssemblyMissingAppKey()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance',
            '_body' => '{"openid":"oUpF8muMJAaName"}',
            'access_token' => 'test_access_token',
        ]);

        $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
        $config->getVirtualPay()->setAppKey(null);

        $rocket = (new Rocket())->setPayload($payload);

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_WECHAT_INVALID);
        self::expectExceptionMessage('配置异常: 缺少微信虚拟支付配置 -- [virtual_pay.app_key]');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testAssemblySandboxEnv()
    {
        $body = '{"openid":"oUpF8muMJAaName"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance',
            '_body' => $body,
            'access_token' => 'test_access_token',
            "env" => 1,
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();
        $url = $resultPayload->get('_url');

        $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
        $sandboxAppKey = $config->getVirtualPay()->getAppKey(1);
        $expectedPaySig = hash_hmac('sha256', '/xpay/query_user_balance&'.$body, $sandboxAppKey);
        self::assertStringContainsString('pay_sig='.$expectedPaySig, $url);
    }

    public function testAssemblyUrlAlreadyHasQueryParams()
    {
        $body = '{"openid":"oUpF8muMJAaName"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance?existing_param=value',
            '_body' => $body,
            'access_token' => 'test_access_token',
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();
        $url = $resultPayload->get('_url');

        self::assertStringContainsString('existing_param=value', $url);
        self::assertStringContainsString('access_token=test_access_token', $url);
        self::assertStringContainsString('pay_sig=', $url);
    }

    public function testAssemblyClientSigningWithoutAccessToken()
    {
        $body = '{"buyQuantity":1,"productId":"test_product","goodsPrice":10,"offerId":"1234567890","currencyType":"CNY"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'requestVirtualPayment',
            '_body' => $body,
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        // 客户端签名场景不传 access_token 不应抛异常
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();
        $url = $resultPayload->get('_url');

        self::assertStringContainsString('pay_sig=', $url);
        self::assertStringNotContainsString('access_token=', $url);

        // paySig 可作为 payload 独立字段访问
        self::assertNotNull($resultPayload->get('paySig'));
    }

    public function testAssemblyServerSideMissingAccessToken()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance',
            '_body' => '{"openid":"oUpF8muMJAaName"}',
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        // 服务端 API 场景缺少 access_token 且未配置 app_secret 应抛异常
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付缺少 access_token，请配置 [virtual_pay.app_secret] 以自动获取，或在参数中手动传入 access_token');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testAssemblyServerSideAutoFetchAccessToken(): void
    {
        $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
        self::assertInstanceOf(WechatConfig::class, $config);
        $config->getVirtualPay()->setAppSecret('test_app_secret');
        $config->getVirtualPay()->setAccessToken('cached_virtual_token_123');
        $config->getVirtualPay()->setAccessTokenExpiry(time() + 3600);

        Pay::config([
            'wechat' => [
                'default' => $config->toArray(),
            ],
            '_force' => true,
        ]);

        $body = '{"openid":"oUpF8muMJAaName"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/present_currency',
            '_body' => $body,
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        // 服务端 API 场景缺少 access_token 但配置了 app_secret，应自动获取（缓存命中，不发起真实请求）
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();
        $url = $resultPayload->get('_url');

        self::assertStringContainsString('access_token=cached_virtual_token_123', $url);
        self::assertStringContainsString('pay_sig=', $url);

        // paySig 可作为 payload 独立字段访问
        self::assertNotNull($resultPayload->get('paySig'));
    }

    public function testAssemblyClientSigningReturnsSignDataString(): void
    {
        $body = '{"buyQuantity":1,"productId":"test_product","goodsPrice":10,"offerId":"1234567890","currencyType":"CNY"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'requestVirtualPayment',
            '_body' => $body,
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();

        // signData 应与参与签名计算的 body 逐字节一致
        self::assertSame($body, $resultPayload->get('signData'));

        // _body 不应被修改
        self::assertSame($body, $resultPayload->get('_body'));
    }

    public function testAssemblyServerSideNoSignDataField(): void
    {
        $body = '{"openid":"oUpF8muMJAaName"}';
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/xpay/query_user_balance',
            '_body' => $body,
            'access_token' => 'test_access_token',
        ]);
        $rocket = (new Rocket())->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $resultPayload = $result->getPayload();

        // 服务端 API 场景不应有 signData 字段
        self::assertNull($resultPayload->get('signData'));
    }
}
