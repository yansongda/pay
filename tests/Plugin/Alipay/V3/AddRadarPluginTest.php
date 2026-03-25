<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

/**
 * @internal
 *
 * @coversNothing
 */
class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{"out_trade_no":"123"}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $radar = $result->getRadar();
        self::assertEquals('POST', $radar->getMethod());
        self::assertStringStartsWith('https://openapi.alipay.com/v3/alipay/trade/query', (string) $radar->getUri());
        self::assertStringContainsString('ALIPAY-SHA256withRSA', $radar->getHeaderLine('Authorization'));
        self::assertEquals('application/json; charset=utf-8', $radar->getHeaderLine('Content-Type'));
    }

    public function testSandboxUrl()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3', '_sandbox' => true])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEmpty($result->getRadar()->getHeaderLine('Authorization'));
    }

    public function testAuthorizationHeaderFormat()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $auth = $result->getRadar()->getHeaderLine('Authorization');
        self::assertStringStartsWith('ALIPAY-SHA256withRSA ', $auth);
        self::assertStringContainsString('app_id=', $auth);
        self::assertStringContainsString('app_cert_sn=', $auth);
        self::assertStringContainsString('nonce=', $auth);
        self::assertStringContainsString('timestamp=', $auth);
        self::assertStringContainsString('sign=', $auth);
    }

    public function testBodyPassedToRequest()
    {
        $body = '{"out_trade_no":"test123"}';

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => $body,
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals($body, (string) $result->getRadar()->getBody());
    }

    public function testGetMethod()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'GET',
                '_body' => '',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('GET', $result->getRadar()->getMethod());
    }

    public function testAppAuthToken()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
                'app_auth_token' => 'test_app_auth_token',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $auth = $result->getRadar()->getHeaderLine('Authorization');
        self::assertStringContainsString('sign=', $auth);
        self::assertEquals('test_app_auth_token', $result->getRadar()->getHeaderLine('alipay-app-auth-token'));
    }

    public function testAppAuthTokenNotSetWhenEmpty()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEmpty($result->getRadar()->getHeaderLine('alipay-app-auth-token'));
    }

    public function testAlipayRequestId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $requestId = $result->getRadar()->getHeaderLine('alipay-request-id');
        self::assertNotEmpty($requestId);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $requestId);
    }

    public function testMissingSecretCert()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

        Pay::config(['alipay' => ['v3_no_secret' => [
            'api_version' => 'v3',
            'app_id' => '9021000122682882',
            'app_public_cert_path' => __DIR__.'/../../../Cert/alipayAppPublicCert.crt',
        ]]]);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3_no_secret'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingAppPublicCertPath()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

        Pay::config(['alipay' => ['v3_no_cert' => [
            'api_version' => 'v3',
            'app_id' => '9021000122682882',
            'app_secret_cert' => 'MIIEpAIBAAKCAQEApSA9oxvcqfbgpgkxXvyCpnxaR6TPaEMh/ij+PhF8180zL82ic4whkrRlcu1Y179AKEZNar71Ugi37fKcXWLerjPOeb8WHnZgNG19gkAcOIqZPRPpJ1eRtwKEclIzt+j3H/wgXWkD7BTr61RjuAcviyvDVbAJ/TPlMqXdJFIuJwZblN2WblIv+4Dm1iPOB+fVCU3rsgg4eajf3HrZ7sq6fBhQhO5krDmIIYGsFZ+fohEgnLkBaF0gqNUb5Yb4PBfaEcu8Hcwq+XyBSMOVOIABRPQVDedW2sE/2NsLkR62DaEe/Ri9VUDJe0pE39P+X22DicJ3E3yrxvdioMnLtDqEuwIDAQABAoIBAQCSHZ1tH9J7c8IGKkxNyROzToZ0rxn5IK6LwKp5MfBO5X1N56DArldnAcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6gAoXN8OXO8jmZg/6fOvFnrGl6CBp8sioe5F3R023fDum546IqS8EZdCl5T0gW/boTbSV8luitab65xBO3PmUI+V2OEFCL6WcJxawBAoGBAOZoft6/LatdoXzr8vh+rKzacUHw246fpacbgx0B5DDymM7hbhXbY/NoCWPgBJtV3XI3DtMJ5yvlEVDQvPfbSHRPx2XQknwrM7ly2SLbaC+tuhcvoG6F1RLWFx+y/583seSlVNuWC9KdpLTKzo8wl8Z4/kheLTBxTxL20NZu79XBAoGBALd3fNoXk5V+T16hnSinPtt2NEsZpn+4w07DikzcpdyjCL5PYjp/BppmX3xly96fCZh3MO3Vkuya1xgauMzxVKQlR/aD5yVmsqK7wxNTY1ZQM74B44/4Mks/8MG2r7o3DElA4/qIeMP4CwkWmYcuij7npm2bgIqFzS+4aGZfDRF7AoGAKMO2Jpy2bMo9BwgLzdFDpbVkMmF1xu8R9NXWRayO/eX+CSQzQOS281qlxqjcx8rSSiHZmpb28notrRmxRTzjvchbo/TZ5eQS262pIxSkg0L+WJnRjZxaDWIZZz9ZIIdPDv/9WnhakSHZAS+cihLz12aSvqUC4744WkeWvUmVX0ECgYAGLDoCKHrps7c96tgbzwy5W4/E2xcUAwZnNwMHNQFLnBymMouOhkmVlk4uJEqosdcjzxbRWbc4yLjl8bg4BQKhBzQVojh7tKnb+c9Fbi/QbqBfCzc519LxXzRdgCUHceSy7kD9Y+wUQ9szMhR2TOWP2kFqPKolfvz5Vw4EK7yH0wKBgQDerq9Pthbii7lNt528/q0cH9vOMn9z76o6jMMea9EibclVHtdcQBWLOn8Yw97k+WSXYGuUrQUWWQbyabZqWkkS4cEjJf5/DiwOuYdNVXg7FK56ucTczBA7lR4dnunPW6U1HbSWf0Cn4Y/cl/z7B5QBSQt0W38IYHSaf6/sqsV6SA==',
        ]]]);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3_no_cert'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testAppCertSnCached()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        // Call again - should use cached app_public_cert_sn
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertStringContainsString('app_cert_sn=', $result->getRadar()->getHeaderLine('Authorization'));
    }

    public function testUrlWithQueryString()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => 'https://openapi.alipay.com/v3/alipay/trade/query?foo=bar',
                '_method' => 'GET',
                '_body' => '',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $uri = (string) $result->getRadar()->getUri();
        self::assertStringContainsString('foo=bar', $uri);
    }

    public function testUserAgent()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setPayload(new Collection([
                '_url' => '/v3/alipay/trade/query',
                '_method' => 'POST',
                '_body' => '{}',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('yansongda/pay-v3', $result->getRadar()->getHeaderLine('User-Agent'));
    }
}
