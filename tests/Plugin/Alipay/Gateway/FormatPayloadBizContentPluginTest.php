<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Gateway;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\Gateway\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

/**
 * @internal
 *
 * @coversNothing
 */
class FormatPayloadBizContentPluginTest extends TestCase
{
    protected FormatPayloadBizContentPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new FormatPayloadBizContentPlugin();
    }

    public function testNormal(): void
    {
        $result = $this->plugin->assembly((new Rocket())->setPayload(new Collection([
            'biz_content' => ['out_trade_no' => 'yansongda-1622986519', '_ignore' => true],
        ])), fn ($rocket) => $rocket);

        self::assertEquals('{"out_trade_no":"yansongda-1622986519"}', $result->getPayload()->get('biz_content'));
    }
}
