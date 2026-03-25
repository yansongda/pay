<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Fund\Transfer\Fund;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund\TransferPlugin;
use Yansongda\Pay\Tests\TestCase;

class TransferPluginTest extends TestCase
{
    protected TransferPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TransferPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams(['out_biz_no' => 'test123']), fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/fund/trans/uni/transfer', $result->getPayload()->get('_url'));
        self::assertEquals('test123', $result->getPayload()->get('_body')['out_biz_no']);
        self::assertEquals('DIRECT_TRANSFER', $result->getPayload()->get('_body')['biz_scene']);
        self::assertEquals('TRANS_ACCOUNT_NO_PWD', $result->getPayload()->get('_body')['product_code']);
    }
}
