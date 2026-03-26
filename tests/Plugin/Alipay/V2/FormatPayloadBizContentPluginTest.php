<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Artful\Rocket;
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

    public function testSignNormal(): void
    {
        $payload = [
            "biz_content" => ['out_trade_no' => "yansongda-1622986519"],
        ];

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('{"out_trade_no":"yansongda-1622986519"}', $result->getPayload()->get('biz_content'));
    }

    public function testSignUnderlineParams(): void
    {
        $payload = [
            "biz_content" => ['out_trade_no' => "yansongda-1622986519", '_method' => 'get', '_ignore' => true],
        ];

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('{"out_trade_no":"yansongda-1622986519"}', $result->getPayload()->get('biz_content'));
    }
}
