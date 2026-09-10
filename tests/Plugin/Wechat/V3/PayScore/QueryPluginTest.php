<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\QueryPlugin;
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

    public function testMissingQueryId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'service_id' => '12345678901234567890123456789012',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testQueryByOutOrderNo()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => '/v3/payscore/serviceorder?out_order_no=202409101234567890&service_id=12345678901234567890123456789012&appid=wx55955316af4ef13',
        ], $result->getPayload()->all());
    }

    public function testQueryByQueryId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'query_id' => '156439172635391389723423423',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => '/v3/payscore/serviceorder?query_id=156439172635391389723423423&service_id=12345678901234567890123456789012&appid=wx55955316af4ef13',
        ], $result->getPayload()->all());
    }

    public function testBothParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
            'query_id' => '156439172635391389723423423',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testServiceIdMissing()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
        self::expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
