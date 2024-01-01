<?php

namespace Plugin\Wechat\V3\Extend\Complaints;

use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\UpdateCallbackPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class UpdateCallbackPluginTest extends TestCase
{
    protected UpdateCallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UpdateCallbackPlugin();
    }

    public function testNormal()
    {
        $payload = [
            "url" => "yansongda",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'PUT',
            '_url' => 'v3/merchant-service/complaint-notifications',
            '_service_url' => 'v3/merchant-service/complaint-notifications',
            'url' => 'yansongda',
        ], $result->getPayload()->all());
    }
}
