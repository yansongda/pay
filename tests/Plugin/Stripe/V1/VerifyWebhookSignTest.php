<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Tests\TestCase;

use function Yansongda\Pay\verify_stripe_webhook_sign;

class VerifyWebhookSignTest extends TestCase
{
    public function testLocalhostSkipsVerification()
    {
        $request = new ServerRequest('POST', 'http://localhost', [], '{}');

        // Should not throw — localhost always skips verification
        verify_stripe_webhook_sign($request, []);

        self::assertTrue(true);
    }

    public function testMissingWebhookSecretThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_STRIPE_INVALID);

        verify_stripe_webhook_sign($request, ['_config' => 'no_webhook_secret']);
    }

    public function testEmptySignatureHeaderThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        verify_stripe_webhook_sign($request, []);
    }

    public function testMalformedSignatureHeaderThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [
            'Stripe-Signature' => 'no-equals-sign-here',
        ], '{}');

        self::expectException(InvalidSignException::class);

        verify_stripe_webhook_sign($request, []);
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

        verify_stripe_webhook_sign($request, []);
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

        verify_stripe_webhook_sign($request, []);
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

        // Should not throw
        verify_stripe_webhook_sign($request, []);

        self::assertTrue(true);
    }
}
