<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\AddPayloadSignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

/**
 * @internal
 *
 * @coversNothing
 */
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
}
