<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple\Pay;

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\PayTokenPlugin;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;
use Yansongda\Pay\Tests\TestCase;

class PayTokenPluginTest extends TestCase
{
    protected PayTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayTokenPlugin();
    }

    public function testNormal()
    {
        $token = AppleTokenFactory::makeToken(['applicationPrimaryAccountID' => '123456', 'totalAmount' => '100']);

        $rocket = new Rocket();
        $rocket->setParams(['token' => $token]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(NoHttpRequestDirection::class, $result->getDirection());
        self::assertEquals('123456', $result->getPayload()->get('applicationPrimaryAccountID'));
        self::assertEquals('100', $result->getPayload()->get('totalAmount'));
        self::assertEquals($token['header'], $result->getPayload()->get('_token_header'));
        self::assertSame($result->getDestination(), $result->getPayload());
    }

    public function testUnderscoreToken()
    {
        $token = AppleTokenFactory::makeToken(['applicationPrimaryAccountID' => '888888']);

        $rocket = new Rocket();
        $rocket->setParams(['_token' => $token]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('888888', $result->getPayload()->get('applicationPrimaryAccountID'));
        self::assertEquals($token['header'], $result->getPayload()->get('_token_header'));
    }

    public function testMissingTokenThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

        $rocket = new Rocket();
        $rocket->setParams([]);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testInvalidTokenThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

        $rocket = new Rocket();
        $rocket->setParams(['token' => ['data' => 'invalid']]);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
