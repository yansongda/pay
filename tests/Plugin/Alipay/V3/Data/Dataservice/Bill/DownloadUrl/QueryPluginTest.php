<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin;
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
            'bill_type' => 'trade',
            'bill_date' => '2025-05-01',
        ]), fn ($rocket) => $rocket);

        self::assertSame('GET', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/data/dataservice/bill/downloadurl/query?bill_type=trade&bill_date=2025-05-01', $result->getPayload()->get('_url'));
        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
