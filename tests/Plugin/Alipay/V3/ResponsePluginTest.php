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

    public function test2xxPass(): void
    {
        foreach ([200, 201, 299] as $statusCode) {
            $rocket = (new Rocket())->setDestinationOrigin(new Response($statusCode, [], json_encode(['code' => '10000'])));
            $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

            self::assertSame($rocket, $result);
        }
    }

    public function testNon2xxThrows(): void
    {
        $rocket = (new Rocket())->setDestinationOrigin(
            new Response(400, [], json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误', 'links' => 'https://example.com/solution']))
        );

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);
        self::expectExceptionMessage('支付宝返回状态码异常: [INVALID_PARAMETER] 参数错误，请检查参数是否错误');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testNon2xxWithoutErrorBodyThrows(): void
    {
        $rocket = (new Rocket())->setDestinationOrigin(new Response(500, [], ''));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);
        self::expectExceptionMessage('支付宝返回状态码异常，请检查参数是否错误');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
