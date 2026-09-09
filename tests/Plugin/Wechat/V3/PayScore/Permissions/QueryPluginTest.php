<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore\Permissions;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询支付分预授权（签约），参数缺少 `authorization_code`');

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

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/payscore/permissions/authorization-code/AUTH_CODE?service_id=12345678901234567890123456789012', $result->getPayload()->get('_url'));
    }

    public function testPayloadServiceIdOverrideConfig()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'authorization_code' => 'AUTH_CODE',
            'service_id' => '99999',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('/v3/payscore/permissions/authorization-code/AUTH_CODE?service_id=99999', $result->getPayload()->get('_url'));
    }
}
