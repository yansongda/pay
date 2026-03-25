<?php

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\GetClientTokenResponsePlugin;
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

    public function testNormal()
    {
        $destination = ['data' => ['access_token' => 'test_token', 'expires_in' => 7200, 'error_code' => 0]];

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response());
        $rocket->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals('test_token', $result->getDestination()->get('data.access_token'));
    }

    public function testHttpStatusCodeError()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(500));
        $rocket->setDestination(new Collection());

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testBusinessError()
    {
        $destination = ['data' => ['error_code' => 10007, 'description' => 'invalid client_key']];

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response());
        $rocket->setDestination(new Collection($destination));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
