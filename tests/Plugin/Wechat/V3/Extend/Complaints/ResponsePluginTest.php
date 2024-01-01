<?php

namespace Plugin\Wechat\V3\Extend\Complaints;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\ResponsePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ResponsePluginTest extends TestCase
{
    protected ResponsePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponsePlugin();
    }

    public function testEmptyComplaintId()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 回复用户，参数缺少 `complaint_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPayloadMchIdEmpty()
    {
        $payload = [
            "complaint_id" => "yansongda",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/merchant-service/complaints-v2/yansongda/response',
            '_service_url' => 'v3/merchant-service/complaints-v2/yansongda/response',
            'complainted_mchid' => '1600314069',
        ], $result->getPayload()->all());
    }

    public function testPayloadMchIdNotEmpty()
    {
        $payload = [
            "complaint_id" => "yansongda",
            'complainted_mchid' => '123',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/merchant-service/complaints-v2/yansongda/response',
            '_service_url' => 'v3/merchant-service/complaints-v2/yansongda/response',
            'complainted_mchid' => '123',
        ], $result->getPayload()->all());
    }

    public function testPayloadOthers()
    {
        $payload = [
            "complaint_id" => "yansongda",
            'complainted_mchid' => '123',
            'name' => 'yansongda',
            'test' => 'abc',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/merchant-service/complaints-v2/yansongda/response',
            '_service_url' => 'v3/merchant-service/complaints-v2/yansongda/response',
            'complainted_mchid' => '123',
            'name' => 'yansongda',
            'test' => 'abc',
        ], $result->getPayload()->all());
    }
}
