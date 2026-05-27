<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class VerifySignaturePluginTest extends TestCase
{
    protected VerifySignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VerifySignaturePlugin();
    }

    public function testShouldNotDoRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        self::assertSame($rocket, $result);

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(null);
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        self::assertSame($rocket, $result);
    }

    public function testErrcodeZeroPassesThrough()
    {
        $response = new Response(
            200,
            [],
            json_encode(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => 100]], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => 100]]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
        self::assertSame(0, $result->getDestination()->get('errcode'));
    }

    public function testErrcodeNonZeroThrowsException()
    {
        $response = new Response(
            200,
            [],
            json_encode(['errcode' => 43001, 'errmsg' => 'invalid credential, access_token is invalid or not expired'], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => 43001, 'errmsg' => 'invalid credential, access_token is invalid or not expired']));

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
        $this->expectExceptionMessage('微信虚拟支付返回业务异常: invalid credential, access_token is invalid or not expired');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testHttpStatusCodeErrorStillChecksErrcode()
    {
        $response = new Response(
            500,
            [],
            json_encode(['errcode' => -1, 'errmsg' => 'system error'], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => -1, 'errmsg' => 'system error']));

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNoDestinationOriginReturnsRocket()
    {
        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }
}
