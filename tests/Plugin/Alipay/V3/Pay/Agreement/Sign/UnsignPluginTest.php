<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Agreement\Sign;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Agreement\Sign\UnsignPlugin;
use Yansongda\Pay\Tests\TestCase;

class UnsignPluginTest extends TestCase
{
    protected UnsignPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UnsignPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'agreement_no' => '202603250001',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/user/agreement/unsign', $result->getPayload()->get('_url'));
        self::assertSame(['agreement_no' => '202603250001'], $result->getPayload()->get('_body'));
    }
}
