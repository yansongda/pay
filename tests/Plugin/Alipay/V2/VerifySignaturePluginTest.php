<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\AlipayV2VerifySignatureHelpers;

uses(AlipayV2VerifySignatureHelpers::class);

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

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, null, \Yansongda\Pay\Exception\Exception::SIGN_ERROR);

it('待签名内容错误', function () {
    $plugin = new VerifySignaturePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::RESPONSE_EMPTY);

it('单字段字符串的明文响应', function () {
    // 明文响应拆包后即使恰好单键且值为 string（如响应仅含 code 字段），签名源也必须按明文形态组串，不得误判为密文.
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant();

    // 签名源 = 明文形态 `{"code":"10000"}`
    openssl_sign('{"code":"10000"}', $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipayAppSecretCert.pem'))), OPENSSL_ALGO_SHA256);

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
    // 加密响应场景下若按旧版对象形态组串签名（签名源错误），必须验签失败.
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant();

    $encrypted = 'hJK2YhT7G0AcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6g==';

    // 以旧版逻辑组串方式（json_encode 整个 result，`/` 会被转义为 `\/`）签名，签名源与密文形态不匹配
    openssl_sign(json_encode(['xxx_response' => $encrypted], JSON_UNESCAPED_UNICODE), $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipayAppSecretCert.pem'))), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'xxx'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $encrypted,
        ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, null, \Yansongda\Pay\Exception\Exception::SIGN_ERROR);

it('加密响应解密', function () {
    // 加密响应：签名源为带双引号的密文原文（与官方 SDK 取串行为一致），
    // 验签通过后自动解密拆包，交付明文业务字段并保留 _sign.
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
    // 安全回归：验签失败时解密不可达，destination 保持密文形态（encrypt-then-MAC，解密原语门控于验签之后）.
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
    // 明文响应即使已配置 aes_key，也不进入解密分支.
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
    // 加密响应验签通过但未配置 aes_key → InvalidConfigException（仅在验签通过后抛出）.
    $plugin = new VerifySignaturePlugin();
    $this->reRegisterAlipayTenant();

    $cipher = base64_encode('fake-cipher');
    openssl_sign('"'.$cipher.'"', $sign, $this->getTestPrivateKey(), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $cipher,
        ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidConfigException::class, null, \Yansongda\Pay\Exception\Exception::DECRYPT_ALIPAY_AES_KEY_INVALID);

it('加密响应 JSON 非法', function () {
    // 验签通过但密文解密后不是合法 JSON → DecryptException.
    $plugin = new VerifySignaturePlugin();
    $key = random_bytes(16);
    $cipher = base64_encode(
        openssl_encrypt('not-a-json-plain', 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16))
    );

    $this->reRegisterAlipayTenant(base64_encode($key));

    openssl_sign('"'.$cipher.'"', $sign, $this->getTestPrivateKey(), OPENSSL_ALGO_SHA256);

    $rocket = (new Rocket())
        ->mergePayload(['method' => 'alipay.user.info.share'])
        ->setDestination(new Collection([
            '_sign' => base64_encode($sign),
            '_cipher' => $cipher,
        ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(DecryptException::class, '加密解密异常: 支付宝密文解密后不是合法的 JSON 响应', \Yansongda\Pay\Exception\Exception::DECRYPT_ALIPAY_ENCRYPTED_DATA_INVALID);

it('无 HTTP 请求方向时不操作', function () {
    // 非 HTTP 请求方向（如回调链路复用）不验签不解密.
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
