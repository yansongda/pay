<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V2\ResponseDecryptPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ResponseDecryptPluginTest extends TestCase
{
    private ResponseDecryptPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponseDecryptPlugin();
    }

    public function testDecryptedResponseParams(): void
    {
        $key = random_bytes(16);
        $cipher = base64_encode(
            openssl_encrypt('{"code":"10000","mobile":"13800000000"}', 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16))
        );

        Pay::config(array_merge($this->getAlipayDefaultConfig(base64_encode($key)), ['_force' => true]));

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.user.info.share'])
            ->setDestination(new Collection([
                '_sign' => 'x',
                'alipay_user_info_share_response' => $cipher,
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        self::assertSame('13800000000', $result->getDestination()->get('mobile'));
        self::assertSame('10000', $result->getDestination()->get('code'));
        self::assertSame('x', $result->getDestination()->get('_sign'));
    }

    public function testPlainResponseNoop(): void
    {
        $destination = [
            '_sign' => 'x',
            'alipay_user_info_share_response' => ['code' => '10000', 'mobile' => '13800000000'],
        ];

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.user.info.share'])
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        self::assertSame($destination, $result->getDestination()->all());
    }

    public function testEncryptedResponseWithoutAesKey(): void
    {
        Pay::config(array_merge($this->getAlipayDefaultConfig(), ['_force' => true]));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::DECRYPT_ALIPAY_AES_KEY_INVALID);

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.user.info.share'])
            ->setDestination(new Collection([
                '_sign' => 'x',
                'alipay_user_info_share_response' => base64_encode('fake-cipher'),
            ]));

        $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });
    }

    public function testNoHttpRequestDirectionNoop(): void
    {
        $destination = [
            '_sign' => 'x',
            'alipay_user_info_share_response' => 'base64密文串',
        ];

        $rocket = (new Rocket())
            ->setDirection(NoHttpRequestDirection::class)
            ->mergePayload(['method' => 'alipay.user.info.share'])
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($destination, $result->getDestination()->all());
    }

    public function testNonCollectionDestinationNoop(): void
    {
        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.user.info.share'])
            ->setDestination(new Response());

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testEncryptedResponseBadJson(): void
    {
        $key = random_bytes(16);
        $cipher = base64_encode(
            openssl_encrypt('not-a-json-plain', 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16))
        );

        Pay::config(array_merge($this->getAlipayDefaultConfig(base64_encode($key)), ['_force' => true]));

        self::expectException(DecryptException::class);
        self::expectExceptionCode(Exception::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID);
        self::expectExceptionMessage('加密解密异常: 支付宝密文解密后不是合法的 JSON 响应');

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.user.info.share'])
            ->setDestination(new Collection([
                '_sign' => 'x',
                'alipay_user_info_share_response' => $cipher,
            ]));

        $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });
    }

    private function getAlipayDefaultConfig(?string $aesKey = null): array
    {
        $default = [
            'app_id' => '9021000122682882',
            'app_secret_cert' => 'MIIEpAIBAAKCAQEApSA9oxvcqfbgpgkxXvyCpnxaR6TPaEMh/ij+PhF8180zL82ic4whkrRlcu1Y179AKEZNar71Ugi37fKcXWLerjPOeb8WHnZgNG19gkAcOIqZPRPpJ1eRtwKEclIzt+j3H/wgXWkD7BTr61RjuAcviyvDVbAJ/TPlMqXdJFIuJwZblN2WblIv+4Dm1iPOB+fVCU3rsgg4eajf3HrZ7sq6fBhQhO5krDmIIYGsFZ+fohEgnLkBaF0gqNUb5Yb4PBfaEcu8Hcwq+XyBSMOVOIABRPQVDedW2sE/2NsLkR62DaEe/Ri9VUDJe0pE39P+X22DicJ3E3yrxvdioMnLtDqEuwIDAQABAoIBAQCSHZ1tH9J7c8IGKkxNyROzToZ0rxn5IK6LwKp5MfBO5X1N56DArldnAcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6gAoXN8OXO8jmZg/6fOvFnrGl6CBp8sioe5F3R023fDum546IqS8EZdCl5T0gW/boTbSV8luitab65xBO3PmUI+V2OEFCL6WcJxawBAoGBAOZoft6/LatdoXzr8vh+rKzacUHw246fpacbgx0B5DDymM7hbhXbY/NoCWPgBJtV3XI3DtMJ5yvlEVDQvPfbSHRPx2XQknwrM7ly2SLbaC+tuhcvoG6F1RLWFx+y/583seSlVNuWC9KdpLTKzo8wl8Z4/kheLTBxTxL20NZu79XBAoGBALd3fNoXk5V+T16hnSinPtt2NEsZpn+4w07DikzcpdyjCL5PYjp/BppmX3xly96fCZh3MO3Vkuya1xgauMzxVKQlR/aD5yVmsqK7wxNTY1ZQM74B44/4Mks/8MG2r7o3DElA4/qIeMP4CwkWmYcuij7npm2bgIqFzS+4aGZfDRF7AoGAKMO2Jpy2bMo9BwgLzdFDpbVkMmF1xu8R9NXWRayO/eX+CSQzQOS281qlxqjcx8rSSiHZmpb28notrRmxRTzjvchbo/TZ5eQS262pIxSkg0L+WJnRjZxaDWIZZz9ZIIdPDv/9WnhakSHZAS+cihLz12aSvqUC4744WkeWvUmVX0ECgYAGLDoCKHrps7c96tgbzwy5W4/E2xcUAwZnNwMHNQFLnBymMouOhkmVlk4uJEqosdcjzxbRWbc4yLjl8bg4BQKhBzQVojh7tKnb+c9Fbi/QbqBfCzc519LxXzRdgCUHceSy7kD9Y+wUQ9szMhR2TOWP2kFqPKolfvz5Vw4EK7yH0wKBgQDerq9Pthbii7lNt528/q0cH9vOMn9z76o6jMMea9EibclVHtdcQBWLOn8Yw97k+WSXYGuUrQUWWQbyabZqWkkS4cEjJf5/DiwOuYdNVXg7FK56ucTczBA7lR4dnunPW6U1HbSWf0Cn4Y/cl/z7B5QBSQt0W38IYHSaf6/sqsV6SA==',
            'app_auth_token' => '',
            'app_public_cert_path' => __DIR__.'/../../../Cert/alipayAppPublicCert.crt',
            'alipay_public_cert_path' => __DIR__.'/../../../Cert/alipayPublicCert.crt',
            'alipay_root_cert_path' => __DIR__.'/../../../Cert/alipayRootCert.crt',
            'notify_url' => 'https://pay.yansongda.cn',
            'return_url' => 'https://pay.yansongda.cn',
        ];

        if (null !== $aesKey) {
            $default['aes_key'] = $aesKey;
        }

        return ['alipay' => ['default' => $default]];
    }
}
