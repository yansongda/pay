<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Fapiao;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\GetTitleUrlPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class GetTitleUrlPluginTest extends TestCase
{
    protected GetTitleUrlPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetTitleUrlPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 获取抬头填写链接，缺少必要参数');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
            'appid' => '1111',
            '_t' => 'a',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/new-tax-control-fapiao/user-title/title-url?test=yansongda&appid=1111',
        ], $result->getPayload()->all());
    }

    public function testNormalWithoutName()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/new-tax-control-fapiao/user-title/title-url?test=yansongda&appid=wx55955316af4ef13',
        ], $result->getPayload()->all());
    }
}
