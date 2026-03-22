<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\WebPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class WebPluginTest extends TestCase
{
    protected WebPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WebPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('v1/checkout/sessions', $payload->get('_url'));
        self::assertEquals('payment', $payload->get('mode'));
    }

    public function testDefaultUrlsFromConfig()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('https://pay.yansongda.cn/stripe/success', $payload->get('success_url'));
        self::assertEquals('https://pay.yansongda.cn/stripe/cancel', $payload->get('cancel_url'));
    }

    public function testCustomUrls()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('https://example.com/success', $payload->get('success_url'));
        self::assertEquals('https://example.com/cancel', $payload->get('cancel_url'));
    }

    public function testCustomMode()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['mode' => 'subscription']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('subscription', $result->getPayload()->get('mode'));
    }
}
