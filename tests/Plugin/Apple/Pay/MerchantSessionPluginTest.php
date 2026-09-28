<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin;
use Yansongda\Pay\Tests\TestCase;

class MerchantSessionPluginTest extends TestCase
{
    protected MerchantSessionPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new MerchantSessionPlugin();
    }

    public function testNormal(): void
    {
        $url = 'https://apple-pay-gateway.apple.com/paymentservices/startSession';

        $rocket = (new Rocket())->setParams([
            'validation_url' => $url,
            'initiative_context' => 'https://shop.yansongda.cn',
        ]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals($url, $payload->get('_url'));
        self::assertTrue($payload->get('_no_jwt'));

        $body = json_decode((string) $payload->get('_body'), true);
        self::assertEquals([
            'merchantIdentifier' => 'merchant.com.yansongda.pay',
            'displayName' => 'merchant.com.yansongda.pay',
            'initiative' => 'web',
            'initiativeContext' => 'https://shop.yansongda.cn',
        ], $body);
    }

    public function testDisplayNameAndInitiativeContextFromParams(): void
    {
        $rocket = (new Rocket())->setParams([
            'validation_url' => 'https://cn-apple-pay-gateway.apple.com/paymentservices/startSession',
            'display_name' => 'Yansongda Store',
            'initiative_context' => 'https://shop.yansongda.cn',
        ]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $body = json_decode((string) $result->getPayload()->get('_body'), true);
        self::assertEquals('merchant.com.yansongda.pay', $body['merchantIdentifier']);
        self::assertEquals('Yansongda Store', $body['displayName']);
        self::assertEquals('web', $body['initiative']);
        self::assertEquals('https://shop.yansongda.cn', $body['initiativeContext']);
    }

    public function testHostCaseInsensitive(): void
    {
        $url = 'https://Apple-Pay-Gateway.APPLE.COM/paymentservices/startSession';

        $rocket = (new Rocket())->setParams([
            'validation_url' => $url,
            'initiative_context' => 'https://shop.yansongda.cn',
        ]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals($url, $result->getPayload()->get('_url'));
    }

    public function testMissingInitiativeContextThrowsException(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = (new Rocket())->setParams(['validation_url' => 'https://apple-pay-gateway.apple.com/paymentservices/startSession']);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testMissingValidationUrlThrowsException(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = (new Rocket())->setParams([]);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testHttpSchemeThrowsException(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_URL_MISSING);

        $rocket = (new Rocket())->setParams(['validation_url' => 'http://apple-pay-gateway.apple.com/paymentservices/startSession']);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testNonAppleHostThrowsException(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_URL_MISSING);

        $rocket = (new Rocket())->setParams(['validation_url' => 'https://evil.com/paymentservices/startSession']);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
