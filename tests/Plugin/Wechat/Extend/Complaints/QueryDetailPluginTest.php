<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Extend\Complaints;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Extend\Complaints\QueryDetailPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryDetailPluginTest extends TestCase
{
    protected QueryDetailPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryDetailPlugin();
    }

    public function testEmptyComplaintId()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询投诉单详情，参数缺少 `complaint_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $payload = [
            "complaint_id" => "yansongda",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/merchant-service/complaints-v2/yansongda',
            '_service_url' => 'v3/merchant-service/complaints-v2/yansongda',
        ], $result->getPayload()->all());
    }

    public function testNormalWithEncryptedContents()
    {
        $payload = [
            "complaint_id" => "yansongda",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) {
            $rocket->setDestination(new Collection([
                'payer_phone' => 'WIesmK+dSJycwdhTTkNmv0Lk2wb9o7NGODovccjhyotNnRkEeh+sxRK1gNSRNMJJgkQ30m4HwcuweSO24mehFeXVNTVAKFVef/3FlHnYDZfE1c3mCLToEef7e8J/Z8TwFH1ecn3t+Jk9ZaBpQKNHdQ0Q8jcL7AnL48h0D9BcZxDekPqX6hNnKfISoKSv4TXFcgvBLFeAe4Q3KM0Snq0N5IvI86D9xZqVg6mY+Gfz0782ymQFxflau6Qxx3mJ+0etHMocNuCdgctVH390XYYMc0u+V2FCJ5cU5h/M/AxzP9ayrEO4l0ftaxL6lP0HjifNrkPcAAb+q9I67UepKO9iGw==',
            ]));

            return $rocket;
        });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/merchant-service/complaints-v2/yansongda',
            '_service_url' => 'v3/merchant-service/complaints-v2/yansongda',
        ], $result->getPayload()->all());
        self::assertEquals('yansongda', $result->getDestination()->all()['payer_phone']);
    }

    public function testNormalWithEncryptedContentsWrong()
    {
        $payload = [
            "complaint_id" => "yansongda",
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::DECRYPT_WECHAT_ENCRYPTED_CONTENTS_INVALID);

        $this->plugin->assembly($rocket, function ($rocket) {
            $rocket->setDestination(new Collection([
                'payer_phone' => 'invalid',
            ]));

            return $rocket;
        });
    }
}
