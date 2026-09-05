<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;
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

    public function testSuccessResponse(): void
    {
        $destination = [
            'err_no' => 0,
            'err_msg' => 'success',
            'log_id' => '20240101123456000000000000',
            'data' => ['order_id' => '123456'],
        ];

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(200));
        $rocket->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals($destination, $result->getDestination()->all());
    }

    public function testBusinessErrorResponseThrowsException(): void
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(200));
        $rocket->setDestination(new Collection([
            'err_no' => 10000,
            'err_msg' => '参数错误',
            'log_id' => '20240101123456000000000000',
        ]));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
        self::expectExceptionMessage('抖音返回业务异常: err_no=10000, err_msg=参数错误');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testServerErrorThrowsException(): void
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(500));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
