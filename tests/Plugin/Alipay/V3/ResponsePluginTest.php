<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
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

    public function testNoHttpRequest()
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setDirection(NoHttpRequestDirection::class);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Rocket::class, $result);
    }

    public function testResponseCodeWrong()
    {
        $this->expectException(\Yansongda\Artful\Exception\InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        $response = new Response(400, [], '{"code":"40004"}');

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptySign()
    {
        $this->expectException(\Yansongda\Pay\Exception\InvalidSignException::class);
        $this->expectExceptionCode(Exception::SIGN_EMPTY);

        $response = new Response(200, [
            'alipay-signature' => '',
            'alipay-timestamp' => '1234567890',
            'alipay-nonce' => 'abc123',
        ], '{"code":"10000"}');

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3'])
            ->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
