<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Subscribe;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\QuerySubscribeContractPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QuerySubscribeContractPluginTest extends TestCase
{
    protected QuerySubscribeContractPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QuerySubscribeContractPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/query_subscribe_contract', $payload->get('_url'));
        self::assertEquals('test_openid', $payload->get('openid'));
        self::assertEquals(0, $payload->get('env'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
        self::assertEquals('/xpay/query_subscribe_contract', $payload->get('_url'));
    }

    public function testDefaultEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(0, $payload->get('env'));
    }
}
