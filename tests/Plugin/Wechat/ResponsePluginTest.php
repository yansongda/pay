<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
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

    public function testOriginalResponseDestination()
    {
        $destination = new Response();

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($destination);
        $rocket->setDestination(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
    }

    public function testOriginalResponseCodeErrorDestination()
    {
        $destination = new Response(500);

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($destination);

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
