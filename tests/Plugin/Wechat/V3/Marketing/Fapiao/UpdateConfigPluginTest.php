<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Fapiao;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\UpdateConfigPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class UpdateConfigPluginTest extends TestCase
{
    protected UpdateConfigPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UpdateConfigPlugin();
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
            'card_appid' => '1111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'PATCH',
            '_url' => 'v3/new-tax-control-fapiao/merchant/development-config',
            "test" => "yansongda",
            'card_appid' => '1111',
        ], $result->getPayload()->all());
    }
}
