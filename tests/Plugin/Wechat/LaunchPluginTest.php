<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Plugin\Wechat\LaunchPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class LaunchPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\LaunchPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new LaunchPlugin();
    }

    public function testShouldNotDoRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestParser::class);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }

    public function testOriginalResponseDestination()
    {
        $destination = new Response();

        $rocket = new Rocket();
        $rocket->setDirection(OriginResponseParser::class);
        $rocket->setDestination($destination);
        $rocket->setDestinationOrigin(new ServerRequest('POST', 'http://localhost'));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($destination, $result->getDestination());
    }

    public function testOriginalResponseCodeErrorDestination()
    {
        $destination = new Response(500);

        $rocket = new Rocket();
        $rocket->setDirection(OriginResponseParser::class);
        $rocket->setDestination($destination);
        $rocket->setDestinationOrigin(new ServerRequest('POST', 'http://localhost'));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::INVALID_RESPONSE_CODE);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testArrayDestination()
    {
        $destination = [];

        $rocket = new Rocket();
        $rocket->setDirection(OriginResponseParser::class);
        $rocket->setDestination($destination);
        $rocket->setDestinationOrigin(new ServerRequest('POST', 'http://localhost'));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals($destination, $result->getDestination());
    }

    public function testCollectionDestination()
    {
        $destination = new Collection();

        $rocket = new Rocket();
        $rocket->setDirection(OriginResponseParser::class);
        $rocket->setDestination($destination);
        $rocket->setDestinationOrigin(new ServerRequest('POST', 'http://localhost'));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($destination, $result->getDestination());
    }
}
