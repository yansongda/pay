<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Fund\Transfer\Fund;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams(['out_biz_no' => 'test123']), fn ($rocket) => $rocket);

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertStringStartsWith('/v3/alipay/fund/trans/common/query?', $result->getPayload()->get('_url'));
        self::assertStringContainsString('out_biz_no=test123', $result->getPayload()->get('_url'));
    }
}
