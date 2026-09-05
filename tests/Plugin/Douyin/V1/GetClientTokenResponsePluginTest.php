<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\GetClientTokenResponsePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class GetClientTokenResponsePluginTest extends TestCase
{
    protected GetClientTokenResponsePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetClientTokenResponsePlugin();
    }

    public function testSuccessResponse(): void
    {
        $destination = [
            'data' => [
                'access_token' => 'clt.9f2c8a1e',
                'expires_in' => 7200,
                'error_code' => 0,
                'description' => '',
            ],
            'message' => 'success',
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
            'data' => [
                'access_token' => '',
                'expires_in' => 0,
                'error_code' => 10013,
                'description' => 'client_key 无效',
            ],
            'message' => 'error',
        ]));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
        self::expectExceptionMessage('获取抖音 client_token 失败: error_code=10013, description=client_key 无效');

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
