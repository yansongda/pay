<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Supports\Collection;

uses(VerifySignaturePluginTestHelpers::class);

it('签名正常', function () {
    $plugin = new VerifySignaturePlugin();
    $destination = [
        '_sign' => 'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==',
        'code' => '10000',
        'msg' => 'Success',
        'order_id' => '20231220110070000002150000657610',
        'out_biz_no' => '2023122022560000',
        'pay_date' => '2023-12-20 22:56:33',
        'pay_fund_order_id' => '20231220110070001502150000660902',
        'status' => 'SUCCESS',
        'trans_amount' => '0.01',
    ];

    $rocket = (new Rocket())
        ->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result)->toBe($rocket);
});

it('签名错误', function () {
    $plugin = new VerifySignaturePlugin();
    $destination = [
        '_sign' => 'AeITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==',
        'code' => '10000',
        'msg' => 'Success',
        'order_id' => '20231220110070000002150000657610',
        'out_biz_no' => '2023122022560000',
        'pay_date' => '2023-12-20 22:56:33',
        'pay_fund_order_id' => '20231220110070001502150000660902',
        'status' => 'SUCCESS',
        'trans_amount' => '0.01',
    ];

    $rocket = (new Rocket())
        ->setDestination(new Collection($destination));

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(\Yansongda\Pay\Exception\Exception::SIGN_ERROR);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('待签名内容错误', function () {
    $plugin = new VerifySignaturePlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::RESPONSE_EMPTY);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('单字段字符串的明文响应', function () {
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant();

    // 签名源 = 明文形态 `{"code":"10000"}`
    openssl_sign('{"code":"10000"}', $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipayAppSecretCert.pem')), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'xxx'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            'code' => '10000',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result)->toBe($rocket);
});

it('加密响应签名来源错误', function () {
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant();

    $encrypted = 'hJK2YhT7G0AcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6g==';

    // 以旧版逻辑组串方式（json_encode 整个 result，`/` 会被转义为 `\/`）签名，签名源与密文形态不匹配
    openssl_sign(json_encode(['xxx_response' => $encrypted], JSON_UNESCAPED_UNICODE), $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipayAppSecretCert.pem')), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'xxx'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $encrypted,
        ]));

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(\Yansongda\Pay\Exception\Exception::SIGN_ERROR);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('加密响应解密', function () {
    $plugin = new VerifySignaturePlugin();
    $key = random_bytes(16);
    $cipher = base64_encode(
        openssl_encrypt('{"code":"10000","mobile":"13800000000"}', 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16))
    );

    $this->reRegisterAlipayTenant(base64_encode($key));

    // 签名源 = 带双引号密文原文（与插件内 json_encode 组串等价）
    openssl_sign('"'.$cipher.'"', $sign, $this->getTestPrivateKey(), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $cipher,
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination()->get('mobile'))->toBe('13800000000');
    expect($result->getDestination()->get('code'))->toBe('10000');
    expect($result->getDestination()->get('_sign'))->toBe(base64_encode($sign));
});

it('加密响应签名错误跳过解密', function () {
    $plugin = new VerifySignaturePlugin();
    $key = random_bytes(16);
    $cipher = base64_encode(
        openssl_encrypt('{"code":"10000","mobile":"13800000000"}', 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16))
    );

    $this->reRegisterAlipayTenant(base64_encode($key));

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection([
            '_sign' => 'wrong-sign',
            '_cipher' => $cipher,
        ]));

    try {
        $plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $this->fail('应当抛出验签失败异常');
    } catch (InvalidSignException $e) {
        // 验签失败 → 解密未执行，密文未被拆包替换
        expect($rocket->getDestination()->get('_cipher'))->toBe($cipher);
    }
});

it('配置 AES 密钥的明文响应', function () {
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant(base64_encode(random_bytes(16)));

    $plain = ['code' => '10000', 'mobile' => '13800000000'];
    openssl_sign(json_encode($plain, JSON_UNESCAPED_UNICODE), $sign, $this->getTestPrivateKey(), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection(array_merge(['_sign' => base64_encode($sign)], $plain)));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result)->toBe($rocket);
    expect($result->getDestination()->get('mobile'))->toBe('13800000000');
});

it('加密响应缺少 AES 密钥', function () {
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant();

    $cipher = base64_encode('fake-cipher');
    openssl_sign('"'.$cipher.'"', $sign, $this->getTestPrivateKey(), OPENSSL_ALGO_SHA256);

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(\Yansongda\Pay\Exception\Exception::DECRYPT_ALIPAY_AES_KEY_INVALID);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $cipher,
        ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('加密响应 JSON 非法', function () {
    $plugin = new VerifySignaturePlugin();
    $key = random_bytes(16);
    $cipher = base64_encode(
        openssl_encrypt('not-a-json-plain', 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16))
    );

    $this->reRegisterAlipayTenant(base64_encode($key));

    openssl_sign('"'.$cipher.'"', $sign, $this->getTestPrivateKey(), OPENSSL_ALGO_SHA256);

    $this->expectException(DecryptException::class);
    $this->expectExceptionCode(\Yansongda\Pay\Exception\Exception::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID);
    $this->expectExceptionMessage('加密解密异常: 支付宝密文解密后不是合法的 JSON 响应');

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $cipher,
        ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('无 HTTP 请求方向时不操作', function () {
    $plugin = new VerifySignaturePlugin();
    $destination = [
        '_sign' => 'x',
        '_cipher' => 'base64密文串',
    ];

    $rocket = (new Rocket())
        ->setDirection(NoHttpRequestDirection::class)
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result)->toBe($rocket);
});

trait VerifySignaturePluginTestHelpers
{
    private function reRegisterAlipayTenant(?string $aesKey = null): void
    {
        Pay::config([
            'alipay' => [
                'default' => array_filter([
                    'app_id' => '9021000122682882',
                    'app_secret_cert' => 'MIIEpAIBAAKCAQEApSA9oxvcqfbgpgkxXvyCpnxaR6TPaEMh/ij+PhF8180zL82ic4whkrRlcu1Y179AKEZNar71Ugi37fKcXWLerjPOeb8WHnZgNG19gkAcOIqZPRPpJ1eRtwKEclIzt+j3H/wgXWkD7BTr61RjuAcviyvDVbAJ/TPlMqXdJFIuJwZblN2WblIv+4Dm1iPOB+fVCU3rsgg4eajf3HrZ7sq6fBhQhO5krDmIIYGsFZ+fohEgnLkBaF0gqNUb5Yb4PBfaEcu8Hcwq+XyBSMOVOIABRPQVDedW2sE/2NsLkR62DaEe/Ri9VUDJe0pE39P+X22DicJ3E3yrxvdioMnLtDqEuwIDAQABAoIBAQCSHZ1tH9J7c8IGKkxNyROzToZ0rxn5IK6LwKp5MfBO5X1N56DArldnAcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6gAoXN8OXO8jmZg/6fOvFnrGl6CBp8sioe5F3R023fDum546IqS8EZdCl5T0gW/boTbSV8luitab65xBO3PmUI+V2OEFCL6WcJxawBAoGBAOZoft6/LatdoXzr8vh+rKzacUHw246fpacbgx0B5DDymM7hbhXbY/NoCWPgBJtV3XI3DtMJ5yvlEVDQvPfbSHRPx2XQknwrM7ly2SLbaC+tuhcvoG6F1RLWFx+y/583seSlVNuWC9KdpLTKzo8wl8Z4/kheLTBxTxL20NZu79XBAoGBALd3fNoXk5V+T16hnSinPtt2NEsZpn+4w07DikzcpdyjCL5PYjp/BppmX3xly96fCZh3MO3Vkuya1xgauMzxVKQlR/aD5yVmsqK7wxNTY1ZQM74B44/4Mks/8MG2r7o3DElA4/qIeMP4CwkWmYcuij7npm2bgIqFzS+4aGZfDRF7AoGAKMO2Jpy2bMo9BwgLzdFDpbVkMmF1xu8R9NXWRayO/eX+CSQzQOS281qlxqjcx8rSSiHZmpb28notrRmxRTzjvchbo/TZ5eQS262pIxSkg0L+WJnRjZxaDWIZZz9ZIIdPDv/9WnhakSHZAS+cihLz12aSvqUC4744WkeWvUmVX0ECgYAGLDoCKHrps7c96tgbzwy5W4/E2xcUAwZnNwMHNQFLnBymMouOhkmVlk4uJEqosdcjzxbRWbc4yLjl8bg4BQKhBzQVojh7tKnb+c9Fbi/QbqBfCzc519LxXzRdgCUHceSy7kD9Y+wUQ9szMhR2TOWP2kFqPKolfvz5Vw4EK7yH0wKBgQDerq9Pthbii7lNt528/q0cH9vOMn9z76o6jMMea9EibclVHtdcQBWLOn8Yw97k+WSXYGuUrQUWWQbyabZqWkkS4cEjJf5/DiwOuYdNVXg7FK56ucTczBA7lR4dnunPW6U1HbSWf0Cn4Y/cl/z7B5QBSQt0W38IYHSaf6/sqsV6SA==',
                    'app_auth_token' => '',
                    'app_public_cert_path' => __DIR__.'/../../../Cert/alipayAppPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__.'/../../../Cert/alipayAppPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__.'/../../../Cert/alipayRootCert.crt',
                    'notify_url' => 'https://pay.yansongda.cn',
                    'return_url' => 'https://pay.yansongda.cn',
                    'aes_key' => $aesKey,
                ], static fn ($v) => null !== $v),
            ],
            '_force' => true,
        ]);
    }
    
    private function getTestPrivateKey()
    {
        return openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipayAppSecretCert.pem'));
    }
}
