<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Transfer\Detail;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Detail\QueryByWxPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryByWxPluginTest extends TestCase
{
    protected QueryByWxPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryByWxPlugin();
    }

    public function testModeWrong()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);
        self::expectExceptionMessage('参数异常: 通过微信明细单号查询明细单，只支持普通商户模式，当前配置为服务商模式');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 通过微信明细单号查询明细单，参数缺少 `batch_id` 或 `detail_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "batch_id" => "111",
            'detail_id' => '222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/transfer/batches/batch-id/111/details/detail-id/222',
        ], $result->getPayload()->all());
    }
}
