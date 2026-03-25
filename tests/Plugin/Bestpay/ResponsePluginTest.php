<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
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

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setDestination(new Collection(['returnCode' => '0000', 'returnMsg' => '成功']))
            ->setDestinationOrigin(new Response(200));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('0000', $result->getDestination()->get('returnCode'));
    }

    public function testHttpError(): void
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $rocket = new Rocket();
        $rocket->setDestination(new Collection([]))
            ->setDestinationOrigin(new Response(500));

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testBusinessError(): void
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);

        $rocket = new Rocket();
        $rocket->setDestination(new Collection(['returnCode' => '9999', 'returnMsg' => '系统错误']))
            ->setDestinationOrigin(new Response(200));

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
