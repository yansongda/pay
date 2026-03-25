<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ResponsePluginTest extends TestCase
{
    protected ResponsePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponsePlugin();
    }

    public function testNoHttpRequest()
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setDirection(NoHttpRequestDirection::class);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Rocket::class, $result);
    }

    public function testResponseCodeWrong()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $response = new Response(400, [], '{"code":"40004"}');

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptySign()
    {
        $this->expectException(InvalidSignException::class);
        $this->expectExceptionCode(Exception::SIGN_EMPTY);

        $response = new Response(200, [
            'alipay-signature' => '',
            'alipay-timestamp' => '1234567890',
            'alipay-nonce' => 'abc123',
        ], '{"code":"10000"}');

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testValidSignature()
    {
        $body = '{"code":"10000","msg":"Success"}';
        $timestamp = '1234567890';
        $nonce = 'abc123';

        $content = $timestamp."\n".$nonce."\n".$body."\n";
        $privKey = openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipayAppSecretCert.pem'));
        openssl_sign($content, $sig, $privKey, OPENSSL_ALGO_SHA256);

        // Override config to use app public cert for verification
        $config = Pay::get(ConfigInterface::class);
        $config->set('alipay.v3_verify', [
            'api_version' => 'v3',
            'app_id' => '9021000122682882',
            'app_secret_cert' => 'MIIEpAIBAAKCAQEApSA9oxvcqfbgpgkxXvyCpnxaR6TPaEMh/ij+PhF8180zL82ic4whkrRlcu1Y179AKEZNar71Ugi37fKcXWLerjPOeb8WHnZgNG19gkAcOIqZPRPpJ1eRtwKEclIzt+j3H/wgXWkD7BTr61RjuAcviyvDVbAJ/TPlMqXdJFIuJwZblN2WblIv+4Dm1iPOB+fVCU3rsgg4eajf3HrZ7sq6fBhQhO5krDmIIYGsFZ+fohEgnLkBaF0gqNUb5Yb4PBfaEcu8Hcwq+XyBSMOVOIABRPQVDedW2sE/2NsLkR62DaEe/Ri9VUDJe0pE39P+X22DicJ3E3yrxvdioMnLtDqEuwIDAQABAoIBAQCSHZ1tH9J7c8IGKkxNyROzToZ0rxn5IK6LwKp5MfBO5X1N56DArldnAcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6gAoXN8OXO8jmZg/6fOvFnrGl6CBp8sioe5F3R023fDum546IqS8EZdCl5T0gW/boTbSV8luitab65xBO3PmUI+V2OEFCL6WcJxawBAoGBAOZoft6/LatdoXzr8vh+rKzacUHw246fpacbgx0B5DDymM7hbhXbY/NoCWPgBJtV3XI3DtMJ5yvlEVDQvPfbSHRPx2XQknwrM7ly2SLbaC+tuhcvoG6F1RLWFx+y/583seSlVNuWC9KdpLTKzo8wl8Z4/kheLTBxTxL20NZu79XBAoGBALd3fNoXk5V+T16hnSinPtt2NEsZpn+4w07DikzcpdyjCL5PYjp/BppmX3xly96fCZh3MO3Vkuya1xgauMzxVKQlR/aD5yVmsqK7wxNTY1ZQM74B44/4Mks/8MG2r7o3DElA4/qIeMP4CwkWmYcuij7npm2bgIqFzS+4aGZfDRF7AoGAKMO2Jpy2bMo9BwgLzdFDpbVkMmF1xu8R9NXWRayO/eX+CSQzQOS281qlxqjcx8rSSiHZmpb28notrRmxRTzjvchbo/TZ5eQS262pIxSkg0L+WJnRjZxaDWIZZz9ZIIdPDv/9WnhakSHZAS+cihLz12aSvqUC4744WkeWvUmVX0ECgYAGLDoCKHrps7c96tgbzwy5W4/E2xcUAwZnNwMHNQFLnBymMouOhkmVlk4uJEqosdcjzxbRWbc4yLjl8bg4BQKhBzQVojh7tKnb+c9Fbi/QbqBfCzc519LxXzRdgCUHceSy7kD9Y+wUQ9szMhR2TOWP2kFqPKolfvz5Vw4EK7yH0wKBgQDerq9Pthbii7lNt528/q0cH9vOMn9z76o6jMMea9EibclVHtdcQBWLOn8Yw97k+WSXYGuUrQUWWQbyabZqWkkS4cEjJf5/DiwOuYdNVXg7FK56ucTczBA7lR4dnunPW6U1HbSWf0Cn4Y/cl/z7B5QBSQt0W38IYHSaf6/sqsV6SA==',
            'app_public_cert_path' => __DIR__.'/../../../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../../../Cert/alipayAppPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../../../Cert/alipayRootCert.crt',
        ]);

        $response = new Response(200, [
            'alipay-signature' => base64_encode($sig),
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
        ], $body);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3_verify'])
            ->setDestinationOrigin($response);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Rocket::class, $result);
    }

    public function testInvalidSignature()
    {
        $this->expectException(InvalidSignException::class);
        $this->expectExceptionCode(Exception::SIGN_ERROR);

        $response = new Response(200, [
            'alipay-signature' => 'invalid_base64_signature',
            'alipay-timestamp' => '1234567890',
            'alipay-nonce' => 'abc123',
        ], '{"code":"10000"}');

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testResponseCode500()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $response = new Response(500, [], '{"code":"50000"}');

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNonResponseDestination()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Rocket::class, $result);
    }
}
