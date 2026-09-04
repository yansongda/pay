<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V2\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\PayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([
            'purchase_units' => [['amount' => ['currency_code' => 'USD', 'value' => '10.00']]],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/v2/checkout/orders', $payload->get('_url'));
        self::assertEquals('CAPTURE', $payload->get('intent'));
    }

    public function testDefaultsFromConfig()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        $context = $payload->get('application_context');

        self::assertEquals('https://pay.yansongda.cn/paypal/return', $context['return_url']);
        self::assertEquals('https://pay.yansongda.cn/paypal/cancel', $context['cancel_url']);
    }

    public function testCustomIntent()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['intent' => 'AUTHORIZE']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('AUTHORIZE', $result->getPayload()->get('intent'));
    }

    public function testApplicationContextParamsNotResidual()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([
            'purchase_units' => [['amount' => ['currency_code' => 'USD', 'value' => '10.00']]],
            'return_url' => 'https://example.com/return',
            'cancel_url' => 'https://example.com/cancel',
            'brand_name' => 'My Shop',
            'landing_page' => 'billing',
            'user_action' => 'PAY_NOW',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        // 已重组进 application_context 的顶层参数应被剔除，避免残留在最终请求 body 中
        self::assertNull($payload->get('return_url'));
        self::assertNull($payload->get('cancel_url'));
        self::assertNull($payload->get('brand_name'));
        self::assertNull($payload->get('landing_page'));
        self::assertNull($payload->get('user_action'));

        $context = $payload->get('application_context');
        self::assertEquals('https://example.com/return', $context['return_url']);
        self::assertEquals('https://example.com/cancel', $context['cancel_url']);
        self::assertEquals('My Shop', $context['brand_name']);
    }
}
