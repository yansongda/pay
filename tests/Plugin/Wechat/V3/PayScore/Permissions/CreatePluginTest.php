<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore\Permissions;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\CreatePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreatePluginTest extends TestCase
{
    protected CreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreatePlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 支付分预授权（签约），参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testServiceIdMissing()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
            'authorization_code' => 'AUTH_CODE',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
        self::expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'authorization_code' => 'AUTH_CODE',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => '/v3/payscore/permissions',
            'appid' => 'wx55955316af4ef13',
            'service_id' => '12345678901234567890123456789012',
            'notify_url' => 'https://pay.yansongda.cn',
            'authorization_code' => 'AUTH_CODE',
        ], $result->getPayload()->all());
    }

    public function testPayloadParamsOverrideConfig()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'authorization_code' => 'AUTH_CODE',
            'appid' => 'wx_payload_override',
            'service_id' => '99999',
            'notify_url' => 'https://notify.payload.cn',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('wx_payload_override', $result->getPayload()->get('appid'));
        self::assertEquals('99999', $result->getPayload()->get('service_id'));
        self::assertEquals('https://notify.payload.cn', $result->getPayload()->get('notify_url'));
    }
}
