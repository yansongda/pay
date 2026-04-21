<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
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
    public function testGetStripeUrl(): void
    {
        $config = new StripeConfig([
            'secret_key' => 'sk_test_stripe_secret',
            'webhook_secret' => 'whsec_stripe_webhook_secret',
            'success_url' => 'https://pay.yansongda.cn/stripe/success',
            'cancel_url' => 'https://pay.yansongda.cn/stripe/cancel',
        ]);
        $sandboxConfig = new StripeConfig([
            'secret_key' => 'sk_test_stripe_secret',
            'webhook_secret' => 'whsec_stripe_webhook_secret',
            'success_url' => 'https://pay.yansongda.cn/stripe/success',
            'cancel_url' => 'https://pay.yansongda.cn/stripe/cancel',
            'mode' => Pay::MODE_SANDBOX,
        ]);

        self::assertEquals('https://yansongda.cn', StripeTraitStub::getStripeUrl($config, new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://api.stripe.com/v1/payment_intents', StripeTraitStub::getStripeUrl($config, new Collection(['_url' => 'v1/payment_intents'])));
        self::assertEquals('https://api.stripe.com/v1/payment_intents', StripeTraitStub::getStripeUrl($sandboxConfig, new Collection(['_url' => 'v1/payment_intents'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_STRIPE_URL_MISSING);
        StripeTraitStub::getStripeUrl($config, new Collection([]));
    }

    public function testVerifyStripeWebhookSignEmptySignature(): void
    {
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/stripe/notify', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }
}
