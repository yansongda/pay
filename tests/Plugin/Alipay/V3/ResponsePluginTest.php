<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
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

    public function testNormal()
    {
        $destination = [
            'alipay_trade_query_response' => [
                'code' => '10000',
                'msg' => 'Success',
                'out_trade_no' => 'yansongda-1622986519',
            ],
            'sign' => '123',
        ];

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.trade.query'])
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals(
            array_merge(['_sign' => '123'], $destination['alipay_trade_query_response']),
            $result->getDestination()->all()
        );
    }

    public function testErrorResponseWithNoMethodKey()
    {
        $destination = [
            'alipay_trade_query_response' => [
                'code' => '10000',
                'msg' => 'Success',
            ],
            'sign' => '123',
        ];

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'not.exist'])
            ->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals(array_merge(['_sign' => '123'], $destination), $result->getDestination()->all());
    }

    public function testErrorResponseWithEmptySignKey()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);

        $destination = [
            'alipay_trade_query_response' => [
                'code' => '40002',
                'msg' => 'Invalid Arguments',
                'sub_msg' => '无效的AppID参数',
            ],
            'sign' => '',
        ];

        $rocket = (new Rocket())
            ->mergePayload(['method' => 'alipay.trade.query'])
            ->setDestination(new Collection($destination));

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
