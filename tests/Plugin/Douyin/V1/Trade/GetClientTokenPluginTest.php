<?php

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\GetClientTokenPlugin;
use Yansongda\Pay\Provider\Douyin;
use Yansongda\Pay\Tests\TestCase;

class GetClientTokenPluginTest extends TestCase
{
    protected GetClientTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetClientTokenPlugin();
    }

    public function testNormal()
    {
        $params = ['_config' => 'trade'];
        $rocket = (new Rocket())->setParams($params);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals(Douyin::TRADE_URL[Pay::MODE_SANDBOX].'oauth/client_token/', $payload->get('_url'));
        self::assertEquals('tt_trade_app_id', $payload->get('client_key'));
        self::assertEquals('tt_trade_app_secret', $payload->get('client_secret'));
        self::assertEquals('client_credential', $payload->get('grant_type'));
    }

    public function testMissingAppId()
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_DOUYIN_INVALID);

        $params = ['_config' => 'empty_salt'];
        $rocket = (new Rocket())->setParams($params);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
