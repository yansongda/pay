<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Data\Bill\Ereceipt;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\QueryPlugin;
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
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'file_id' => '202603250001',
        ]), fn ($rocket) => $rocket);

        self::assertSame('GET', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/data/bill/ereceipt/query?file_id=202603250001', $result->getPayload()->get('_url'));
        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
