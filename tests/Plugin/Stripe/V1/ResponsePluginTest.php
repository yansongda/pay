<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
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
        $destination = ['id' => 'pi_test123', 'status' => 'requires_payment_method'];

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(200));
        $rocket->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals($destination, $result->getDestination()->all());
    }

    public function testCreatedResponse()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(201));
        $rocket->setDestination(new Collection(['id' => 'pi_test456']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
    }

    public function testClientErrorThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(400));
        $rocket->setDestination(new Collection(['error' => ['type' => 'invalid_request_error']]));

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

    public function testUnauthorizedThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response(401));
        $rocket->setDestination(new Collection(['error' => ['type' => 'authentication_error']]));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
