<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V1;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V1\ResponsePlugin;
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

    public function testSuccessResponse()
    {
        $destination = ['id' => 'ORDER_123', 'status' => 'CREATED'];

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(201));
        $rocket->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals($destination, $result->getDestination()->all());
    }

    public function testNonSuccessResponseThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(400));
        $rocket->setDestination(new Collection(['error' => 'INVALID_REQUEST']));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testServerErrorThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(500));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
