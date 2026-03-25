<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Tests\TestCase;

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
        $rocket = (new Rocket())->setDestinationOrigin(new Response(200, [], '{"code":"10000"}'));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testErrorResponse()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $rocket = (new Rocket())->setDestinationOrigin(new Response(400, [], '{"message":"invalid request"}'));

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
