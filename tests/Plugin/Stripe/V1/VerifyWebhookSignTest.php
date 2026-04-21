<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\StripeTrait;

class StripeTraitStub
{
    use StripeTrait;
}

class VerifyWebhookSignTest extends TestCase
{
    public function testLocalhostNoLongerSkipsVerification()
    {
        $request = new ServerRequest('POST', 'http://localhost', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }

    public function testMissingWebhookSecretThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_STRIPE_INVALID);

        StripeTraitStub::verifyStripeWebhookSign($request, ['_config' => 'no_webhook_secret']);
    }

    public function testEmptySignatureHeaderThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }

    public function testMalformedSignatureHeaderThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => 'no-equals-sign-here',
        ], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        StripeTraitStub::verifyStripeWebhookSign($request, []);
    }

    public function testExpiredTimestampThrowsException()
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

    public function testWrongSignatureThrowsException()
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

    public function testValidSignaturePasses()
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
}
