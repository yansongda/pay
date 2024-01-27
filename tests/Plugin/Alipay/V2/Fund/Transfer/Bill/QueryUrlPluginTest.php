<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Transfer\Bill;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Bill\QueryUrlPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryUrlPluginTest extends TestCase
{
    protected QueryUrlPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryUrlPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.data.dataservice.bill.downloadurl.query', $payload);
    }
}
