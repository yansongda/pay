<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ResponsePluginTest extends TestCase
{
    public function testSuccess(): void
    {
        $rocket = new Rocket();
        $rocket->setDestination(new Collection([
            'success' => true,
            'result' => ['tradeStatus' => 'SUCCESS'],
        ]));
        $rocket->setDestinationOrigin(new Response(200, [], '{}'));

        $result = (new ResponsePlugin())->assembly($rocket, fn ($r) => $r);

        self::assertTrue($result->getDestination()->get('success'));
    }

    public function testBusinessError(): void
    {
        $this->expectException(InvalidResponseException::class);

        $rocket = new Rocket();
        $rocket->setDestination(new Collection([
            'success' => false,
            'errorCode' => 'API500',
            'errorMsg' => '系统繁忙',
        ]));
        $rocket->setDestinationOrigin(new Response(200, [], '{}'));

        (new ResponsePlugin())->assembly($rocket, fn ($r) => $r);
    }

    public function testHttpError(): void
    {
        $this->expectException(InvalidResponseException::class);

        $rocket = new Rocket();
        $rocket->setDestination(new Collection(['success' => true]));
        $rocket->setDestinationOrigin(new Response(500, [], '{}'));

        (new ResponsePlugin())->assembly($rocket, fn ($r) => $r);
    }
}
