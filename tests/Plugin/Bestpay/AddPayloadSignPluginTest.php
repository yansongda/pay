<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\BestpayTrait;

class AddPayloadSignPluginTest extends TestCase
{
    use BestpayTrait;

    public function testAssembly(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'outTradeNo' => 'ORDER001',
            'tradeAmt' => '99',
            'subject' => '测试',
        ]);

        $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);
        $rocket->mergePayload(['_path' => '/pay/tradeCreate', '_method' => 'POST']);

        $rocket = (new AddPayloadSignPlugin())->assembly($rocket, fn ($r) => $r);

        $payload = $rocket->getPayload();

        self::assertEquals('/pay/tradeCreate', $payload->get('path'));
        self::assertNotEmpty($payload->get('sign'));
        self::assertNotEmpty($payload->get('commonParams'));
        self::assertNotEmpty($payload->get('bizContent'));

        $biz = json_decode((string) $payload->get('bizContent'), true);
        self::assertEquals('ORDER001', $biz['outTradeNo']);
        self::assertEquals('99', $biz['tradeAmt']);
        self::assertEquals('MERCHANT', $biz['institutionType']);

        $common = json_decode((string) $payload->get('commonParams'), true);
        self::assertEquals('MERCHANT', $common['institutionType']);
        self::assertEquals('3178033925245778', $common['institutionCode']);
    }
}
