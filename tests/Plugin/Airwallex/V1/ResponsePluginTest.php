<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
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
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(200));
        $rocket->setDestination(new Collection(['id' => 'int_test_123']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('int_test_123', $result->getDestination()->get('id'));
    }

    public function testCreatedResponse()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(201));
        $rocket->setDestination(new Collection(['id' => 'int_test_456']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('int_test_456', $result->getDestination()->get('id'));
    }

    public function testClientErrorThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(400));
        $rocket->setDestination(new Collection(['code' => 'validation_error']));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testServerErrorThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(500));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
