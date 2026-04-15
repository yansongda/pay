<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\PaypalTrait;
use Yansongda\Supports\Collection;

class PaypalTraitStub
{
    use PaypalTrait;
}

class PaypalTraitTest extends TestCase
{
    public function testGetPaypalUrlDefault(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Paypal::URL[Pay::MODE_NORMAL].'/v2/checkout',
            PaypalTraitStub::getPaypalUrl([], new Collection(['_url' => '/v2/checkout']))
        );
    }

    public function testGetPaypalUrlWithHttp(): void
    {
        self::assertSame(
            'https://example.com/paypal',
            PaypalTraitStub::getPaypalUrl([], new Collection(['_url' => 'https://example.com/paypal']))
        );
    }

    public function testVerifyPaypalWebhookSignMissingConfig(): void
    {
        self::expectException(\Yansongda\Pay\Exception\InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        PaypalTraitStub::verifyPaypalWebhookSign(new ServerRequest('POST', 'https://example.com', [], '{}'), []);
    }
}
