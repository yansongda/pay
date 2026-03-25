<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\Pay\Web\PayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class WebPayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('pay/cashierPay', $payload->get('_url'));
        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('https://pay.yansongda.cn/bestpay/notify', $payload->get('backUrl'));
        self::assertEquals('https://pay.yansongda.cn/bestpay/return', $payload->get('frontUrl'));
    }

    public function testCustomNotifyUrl(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection(['backUrl' => 'https://custom.example.com/notify']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('https://custom.example.com/notify', $result->getPayload()->get('backUrl'));
    }
}
