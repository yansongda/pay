<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Extend\Complaints;

use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\DeleteCallbackPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;

class DeleteCallbackPluginTest extends TestCase
{
    protected DeleteCallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DeleteCallbackPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'DELETE',
            '_url' => 'v3/merchant-service/complaint-notifications',
            '_service_url' => 'v3/merchant-service/complaint-notifications',
        ], $result->getPayload()->all());
    }
}
