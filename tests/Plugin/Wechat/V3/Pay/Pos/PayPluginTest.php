<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Pos;

use Yansongda\Artful\Contract\PackerInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Pos\PayPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 付款码支付，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'description' => 'test',
            "out_trade_no" => "111",
            'payer' => [
                'auth_code' => '1234'
            ],
            'amount' => [
                'total' => 1,
            ],
            'scene_info' => [
                'id' => '5678'
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals(PackerInterface::class, $result->getPacker());
        self::assertEquals('v3/pay/transactions/codepay', $payload->get('_url'));
        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('wx55955316af4ef13', $payload->get('appid'));
        self::assertEquals('1600314069', $payload->get('mchid'));
        self::assertEquals('111', $payload->get('out_trade_no'));
        self::assertEquals('test', $payload->get('description'));
        self::assertEquals('1234', $payload->get('payer')['auth_code']);
        self::assertEquals(1, $payload->get('amount')['total']);
        self::assertEquals('5678', $payload->get('scene_info')['id']);
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'description' => 'test',
            "out_trade_no" => "111",
            'payer' => [
                'auth_code' => '1234'
            ],
            'amount' => [
                'total' => 1,
            ],
            'scene_info' => [
                'id' => '5678'
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals(PackerInterface::class, $result->getPacker());
        self::assertEquals('v3/pay/partner/transactions/codepay', $payload->get('_service_url'));
        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('wx55955316af4ef13', $payload->get('sp_appid'));
        self::assertEquals('1600314069', $payload->get('sp_mchid'));
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
        self::assertEquals('111', $payload->get('out_trade_no'));
        self::assertEquals('test', $payload->get('description'));
        self::assertEquals('1234', $payload->get('payer')['auth_code']);
        self::assertEquals(1, $payload->get('amount')['total']);
        self::assertEquals('5678', $payload->get('scene_info')['id']);
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'sub_mchid' => 'aaaa',
            'description' => 'test',
            "out_trade_no" => "111",
            'payer' => [
                'auth_code' => '1234'
            ],
            'amount' => [
                'total' => 1,
            ],
            'scene_info' => [
                'id' => '5678'
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals(PackerInterface::class, $result->getPacker());
        self::assertEquals('v3/pay/partner/transactions/codepay', $payload->get('_service_url'));
        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('wx55955316af4ef13', $payload->get('sp_appid'));
        self::assertEquals('1600314069', $payload->get('sp_mchid'));
        self::assertEquals('aaaa', $payload->get('sub_mchid'));
        self::assertEquals('111', $payload->get('out_trade_no'));
        self::assertEquals('test', $payload->get('description'));
        self::assertEquals('1234', $payload->get('payer')['auth_code']);
        self::assertEquals(1, $payload->get('amount')['total']);
        self::assertEquals('5678', $payload->get('scene_info')['id']);
    }
}
