<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\StripeTrait;
use Yansongda\Supports\Collection;

class StripeTraitStub
{
    use StripeTrait;
}

class StripeTraitTest extends TestCase
{
    public function testGetStripeUrlDefault(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Stripe::URL[Pay::MODE_NORMAL].'/v1/payment_intents',
            StripeTraitStub::getStripeUrl([], new Collection(['_url' => '/v1/payment_intents']))
        );
    }

    public function testGetStripeUrlWithHttp(): void
    {
        self::assertSame(
            'https://example.com/stripe',
            StripeTraitStub::getStripeUrl([], new Collection(['_url' => 'https://example.com/stripe']))
        );
    }

    public function testVerifyStripeWebhookSignMissingConfig(): void
    {
        self::expectException(\Yansongda\Pay\Exception\InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        StripeTraitStub::verifyStripeWebhookSign(new ServerRequest('POST', 'https://example.com', [], '{}'), []);
    }
}
