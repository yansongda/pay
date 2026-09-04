<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
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
        self::assertEquals('https://api.stripe.com/v1/payment_intents', StripeTraitStub::getStripeUrl($config, new Collection(['_url' => '/v1/payment_intents'])));
        self::assertEquals('https://api.stripe.com/v1/payment_intents', StripeTraitStub::getStripeUrl($sandboxConfig, new Collection(['_url' => '/v1/payment_intents'])));
    }

    public function testGetStripeUrlMissingThrowsException(): void
    {
        $config = new StripeConfig([
            'secret_key' => 'sk_test_stripe_secret',
            'webhook_secret' => 'whsec_stripe_webhook_secret',
        ]);

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

    public function testVerifyStripeWebhookSignMissingWebhookSecretThrowsException(): void
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_STRIPE_INVALID);

        StripeTraitStub::verifyStripeWebhookSign($request, ['_config' => 'no_webhook_secret']);
    }

    public function testVerifyStripeWebhookSignMalformedSignatureHeaderThrowsException(): void
    {
        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => 'no-equals-sign-here',
        ], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }

    public function testVerifyStripeWebhookSignExpiredTimestampThrowsException(): void
    {
        $oldTimestamp = time() - 400;
        $signatureHeader = 't='.$oldTimestamp.',v1=fakesig';

        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => $signatureHeader,
        ], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }

    public function testVerifyStripeWebhookSignWrongSignatureThrowsException(): void
    {
        $timestamp = time();
        $signatureHeader = 't='.$timestamp.',v1=invalidsignaturevalue';

        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => $signatureHeader,
        ], '{"id":"evt_test"}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }

    public function testVerifyStripeWebhookSignValidSignaturePasses(): void
    {
        $body = '{"id":"evt_test","type":"payment_intent.succeeded"}';
        $webhookSecret = 'whsec_stripe_webhook_secret';
        $timestamp = time();
        $signedPayload = $timestamp.'.'.$body;
        $expectedSig = hash_hmac('sha256', $signedPayload, $webhookSecret);
        $signatureHeader = 't='.$timestamp.',v1='.$expectedSig;

        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => $signatureHeader,
        ], $body);

        StripeTraitStub::verifyStripeWebhookSign($request, []);

        self::assertTrue(true);
    }

    public function testVerifyStripeWebhookSignMultipleSignaturesPasses(): void
    {
        $body = '{"id":"evt_test","type":"payment_intent.succeeded"}';
        $webhookSecret = 'whsec_stripe_webhook_secret';
        $timestamp = time();
        $signedPayload = $timestamp.'.'.$body;
        $expectedSig = hash_hmac('sha256', $signedPayload, $webhookSecret);
        $signatureHeader = 't='.$timestamp.',v1=fakesig,v1='.$expectedSig;

        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => $signatureHeader,
        ], $body);

        StripeTraitStub::verifyStripeWebhookSign($request, []);

        self::assertTrue(true);
    }
}
