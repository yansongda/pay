<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Tests\Support\Traits\AlipayV3VerifySignatureHelpers;

uses(AlipayV3VerifySignatureHelpers::class);

it('不应发起请求', function () {
    $plugin = new VerifySignaturePlugin();
    $rocket = (new Rocket())->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    expect($result)->toBe($rocket);

    $rocket = (new Rocket())->setDestinationOrigin(null);
    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    expect($result)->toBe($rocket);
});

it('正常', function () {
    $plugin = new VerifySignaturePlugin();
    $body = json_encode(['code' => '10000', 'msg' => 'Success']);
    $response = $this->makeSignedResponse(200, $body);

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result)->toBe($rocket);
});

it('非 200 签名验签通过', function () {
    $plugin = new VerifySignaturePlugin();
    // 非 200 有签：同样验签，通过后放行进入错误处理
    $body = json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误']);
    $response = $this->makeSignedResponse(400, $body);

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result)->toBe($rocket);
});

it('非 200 无签名通过', function () {
    $plugin = new VerifySignaturePlugin();
    // 非 200 无签：直接放行进入错误处理（不做时间戳校验，对齐官方「有签才验」）
    $response = new Response(400, [], json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误']));

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result)->toBe($rocket);
});

it('非 200 无签名但时间戳过期通过', function () {
    $plugin = new VerifySignaturePlugin();
    // 非 200 无签且时间戳过期：仍应放行（不能被时间戳异常拦截而无法进入业务错误处理）
    $response = new Response(400, ['alipay-timestamp' => '1735689600123', 'alipay-nonce' => 'yansongda-nonce'], '{}');

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result)->toBe($rocket);
});

it('无签名强制验签', function () {
    $plugin = new VerifySignaturePlugin();
    // HTTP 200 强制验签：无签名也必须验签（空签抛异常）
    $response = new Response(200, ['alipay-timestamp' => (string) (int) (microtime(true) * 1000), 'alipay-nonce' => 'yansongda-nonce', 'alipay-sn' => CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt'))], '{}');

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

it('签名无效', function () {
    $plugin = new VerifySignaturePlugin();
    $response = new Response(
        200,
        ['alipay-timestamp' => (string) (int) (microtime(true) * 1000), 'alipay-nonce' => 'yansongda-nonce', 'alipay-signature' => 'invalid-sign', 'alipay-sn' => CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt'))],
        '{}'
    );

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('时间戳过期', function () {
    $plugin = new VerifySignaturePlugin();
    // 毫秒时间戳超过 ±300 秒（换算后）：抛 InvalidSignException
    $timestamp = '1735689600123';
    $nonce = 'yansongda-nonce';
    $body = '{}';
    $sign = $this->sign($timestamp."\n".$nonce."\n".$body."\n");

    $response = new Response(
        200,
        ['alipay-timestamp' => $timestamp, 'alipay-nonce' => $nonce, 'alipay-signature' => base64_encode($sign), 'alipay-sn' => CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt'))],
        $body
    );

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidSignException::class, '签名异常: 支付宝 V3 时间戳已过期', Exception::SIGN_ERROR);

it('sn 不匹配', function () {
    $plugin = new VerifySignaturePlugin();
    $body = json_encode(['code' => '10000']);
    $response = $this->makeSignedResponse(200, $body, 'invalid-alipay-sn');

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidSignException::class, '签名异常: 支付宝公钥证书已过期/不匹配，请重新下载最新支付宝公钥证书并替换', Exception::SIGN_ERROR);

it('缺少 sn', function () {
    $plugin = new VerifySignaturePlugin();
    // 有意偏差：alipay-sn 缺失一律抛异常（官方会回落缓存第一个公钥，此处更严格）
    $body = json_encode(['code' => '10000']);
    $response = $this->makeSignedResponse(200, $body, null);

    $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidSignException::class, '签名异常: 支付宝公钥证书已过期/不匹配，请重新下载最新支付宝公钥证书并替换', Exception::SIGN_ERROR);

it('支付宝 V3 时间戳有效', function () {
    $stub = new class extends VerifySignaturePlugin {
        public function exposeVerifyTimestamp(string $timestamp): void
        {
            $this->verifyAlipayV3Timestamp($timestamp);
        }
    };

    $stub->exposeVerifyTimestamp((string) (time() * 1000));

    expect(true)->toBeTrue();
});

it('支付宝 V3 时间戳过期', function () {
    $stub = new class extends VerifySignaturePlugin {
        public function exposeVerifyTimestamp(string $timestamp): void
        {
            $this->verifyAlipayV3Timestamp($timestamp);
        }
    };

    $stub->exposeVerifyTimestamp('1735689600123');
})->throws(InvalidSignException::class, '签名异常: 支付宝 V3 时间戳已过期', Exception::SIGN_ERROR);
