<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Direction\CollectionDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Bestpay\V1\VerifySignaturePlugin;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

uses(BestpayTrait::class);
uses(VerifySignaturePluginTestHelpers::class);

it('响应签名有效', function () {
    $body = [
        'success' => true,
        'errorCode' => null,
        'errorMsg' => null,
        'result' => ['outTradeNo' => 'ORDER001', 'tradeStatus' => 'SUCCESS'],
    ];
    $body['sign'] = self::signBestpayContent($this->makeConfig(), self::getBestpaySignContent($body));

    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'default']);
    $rocket->setDirection(CollectionDirection::class);
    $rocket->setDestination(new Collection($body));

    $result = (new VerifySignaturePlugin())->assembly($rocket, fn ($r) => $r);

    expect((bool) $result->getDestination()->get('success'))->toBeTrue();
});

it('嵌套结果含斜杠与 Unicode 的响应签名有效', function () {
    // 嵌套 result 含 URL（`/`）与中文：对齐官方 fastjson 序列化（`/` 与中文均不转义），
    // 回归验证 JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE 拼串语义。
    $body = [
        'success' => true,
        'errorCode' => null,
        'errorMsg' => null,
        'result' => [
            'codeUrl' => 'https://qr.bestpay.com.cn/aggpay/1001',
            'subject' => '测试订单',
            'payUrl' => 'https://pay.bestpay.com.cn/mobile/pay?id=1001',
        ],
    ];
    $body['sign'] = self::signBestpayContent($this->makeConfig(), self::getBestpaySignContent($body));

    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'default']);
    $rocket->setDirection(CollectionDirection::class);
    $rocket->setDestination(new Collection($body));

    $result = (new VerifySignaturePlugin())->assembly($rocket, fn ($r) => $r);

    expect((bool) $result->getDestination()->get('success'))->toBeTrue();
    expect($result->getDestination()->get('result.codeUrl'))->toEqual('https://qr.bestpay.com.cn/aggpay/1001');
});

it('响应签名非法', function () {
    $this->expectException(InvalidSignException::class);

    $body = [
        'success' => true,
        'result' => ['outTradeNo' => 'ORDER001'],
        'sign' => base64_encode('not-a-real-sign'),
    ];

    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'default']);
    $rocket->setDirection(CollectionDirection::class);
    $rocket->setDestination(new Collection($body));

    (new VerifySignaturePlugin())->assembly($rocket, fn ($r) => $r);
});

it('响应待签名内容为嵌套 JSON', function () {
    $content = self::getBestpaySignContent([
        'result' => ['b' => 1, 'a' => 2],
        'success' => true,
        'errorCode' => null,
        'sign' => 'x',
    ]);

    expect($content)->toEqual('errorCode=null&result={"a":2,"b":1}&success=true');
});

it('嵌套 JSON 中斜杠不转义', function () {
    // fastjson 默认不转义 `/` 与中文；回归验证 JSON_UNESCAPED_SLASHES。
    $content = self::getBestpaySignContent([
        'result' => [
            'codeUrl' => 'https://qr.bestpay.com.cn/aggpay/1001',
            'subject' => '测试订单',
        ],
        'success' => true,
    ]);

    expect($content)->toEqual('result={"codeUrl":"https://qr.bestpay.com.cn/aggpay/1001","subject":"测试订单"}&success=true');
});

it('待签名内容保留空值', function () {
    // 对齐官方 AssembleSignatureData：null → k=null，空串 → k=
    $content = self::getBestpaySignContent([
        'empty' => '',
        'null' => null,
        'bool' => false,
        'path' => '/pay/tradeCreate',
        'sign' => 'removed',
    ]);

    expect($content)->toEqual('bool=false&empty=&null=null&path=/pay/tradeCreate');
});

trait VerifySignaturePluginTestHelpers
{
    private function makeConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../../Cert/bestpay/bestpay.cer',
        ]);
    }
}
