<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Apple;
use Yansongda\Pay\Tests\TestCase;

class AppleTest extends TestCase
{
    public function testPay()
    {
        $apple = Pay::apple();

        self::assertInstanceOf(Apple::class, $apple);
        self::assertInstanceOf(Apple::class, Pay::get('apple'));
        self::assertInstanceOf(Apple::class, Pay::get(Apple::class));
    }

    public function testCancel()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::apple()->cancel([]);
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::apple()->close([]);
    }

    public function testSuccess()
    {
        $result = Pay::apple()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertEquals(200, $result->getStatusCode());
        self::assertEquals('{"result":"success"}', (string) $result->getBody());
        self::assertStringContainsString('application/json', $result->getHeaderLine('Content-Type'));
    }

    public function testUrl()
    {
        self::assertEquals('https://api.storekit.apple.com', Apple::URL[Pay::MODE_NORMAL]);
        self::assertEquals('https://api.storekit-sandbox.apple.com', Apple::URL[Pay::MODE_SANDBOX]);
        self::assertEquals('https://api.storekit.apple.com', Apple::URL[Pay::MODE_SERVICE]);
    }
}
