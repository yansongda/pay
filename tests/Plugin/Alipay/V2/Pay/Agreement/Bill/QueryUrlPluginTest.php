<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Pay\Agreement\Bill;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Bill\QueryUrlPlugin;
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

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.data.dataservice.bill.downloadurl.query', $result->getPayload()->toJson());
    }
}
