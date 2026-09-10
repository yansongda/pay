<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Direction\CollectionDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Bestpay\V1\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;

class VerifySignaturePluginTest extends TestCase
{
    use BestpayTrait;

    public function testValidResponseSign(): void
    {
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

        self::assertTrue((bool) $result->getDestination()->get('success'));
    }

    public function testInvalidResponseSign(): void
    {
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
    }

    public function testResponseSignContentNestedJson(): void
    {
        $content = self::getBestpaySignContent([
            'result' => ['b' => 1, 'a' => 2],
            'success' => true,
            'errorCode' => null,
            'sign' => 'x',
        ]);

        self::assertEquals(
            'errorCode=null&result={"a":2,"b":1}&success=true',
            $content
        );
    }

    public function testSignContentKeepsEmptyValues(): void
    {
        // 对齐官方 AssembleSignatureData：null → k=null，空串 → k=
        $content = self::getBestpaySignContent([
            'empty' => '',
            'null' => null,
            'bool' => false,
            'path' => '/pay/tradeCreate',
            'sign' => 'removed',
        ]);

        self::assertEquals(
            'bool=false&empty=&null=null&path=/pay/tradeCreate',
            $content
        );
    }

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
