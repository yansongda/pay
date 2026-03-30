<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Pos;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Pos\PayPlugin;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal(): void
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertSame('/v3/alipay/trade/pay', $result->getPayload()->get('_url'));
        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('bar_code', $result->getPayload()->get('_body')['scene']);
    }

    public function testNormalWithCustomScene(): void
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'scene' => 'wave_code',
        ]), fn ($rocket) => $rocket);

        self::assertSame('wave_code', $result->getPayload()->get('_body')['scene']);
    }
}
