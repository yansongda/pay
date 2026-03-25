<?php

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ResponsePlugin;
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
        $destination = ['err_no' => 0, 'err_tips' => 'ok', 'data' => ['foo' => 'bar']];

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(new Response());
        $rocket->setDestination(new Collection($destination));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals($destination, $result->getDestination()->all());
    }

    public function testHttpStatusCodeError()
    {
        $destination = new Response(500);

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($destination);

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testBusinessCodeError()
    {
        $destination = new Response(200);

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($destination);
        $rocket->setDestination(new Collection(['err_no' => 1, 'err_tips' => 'error']));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
