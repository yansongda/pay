<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Data\Bill\Ereceipt;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\ApplyPlugin;
use Yansongda\Pay\Tests\TestCase;

class ApplyPluginTest extends TestCase
{
    protected ApplyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ApplyPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'type' => 'TRANSFER',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/data/bill/ereceipt/apply', $result->getPayload()->get('_url'));
        self::assertSame(['type' => 'TRANSFER'], $result->getPayload()->get('_body'));
    }
}
