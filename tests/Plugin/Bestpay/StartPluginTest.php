<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class StartPluginTest extends TestCase
{
    public function testAssembly(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['outTradeNo' => 'ORDER001', '_config' => 'default']);

        $rocket = (new StartPlugin())->assembly($rocket, fn ($r) => $r);

        $payload = $rocket->getPayload();

        self::assertEquals('ORDER001', $payload->get('outTradeNo'));
        self::assertEquals('MERCHANT', $payload->get('institutionType'));
        self::assertEquals('3178033925245778', $payload->get('institutionCode'));
    }
}
