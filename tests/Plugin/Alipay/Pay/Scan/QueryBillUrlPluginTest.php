<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Pay\Scan;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\QueryBillUrlPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class QueryBillUrlPluginTest extends TestCase
{
    protected QueryBillUrlPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryBillUrlPlugin();
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
