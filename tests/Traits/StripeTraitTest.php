<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\StripeTrait;
use Yansongda\Supports\Collection;

class StripeTraitStub
{
use StripeTrait;
}

it('get stripe url', function () {
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
        'mode' => PayMode::SANDBOX,
    ]);

    expect(StripeTraitStub::getStripeUrl($config, new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');
    expect(StripeTraitStub::getStripeUrl($config, new Collection(['_url' => '/v1/payment_intents'])))->toEqual('https://api.stripe.com/v1/payment_intents');
    expect(StripeTraitStub::getStripeUrl($sandboxConfig, new Collection(['_url' => '/v1/payment_intents'])))->toEqual('https://api.stripe.com/v1/payment_intents');
});

it('get stripe url missing throws exception', function () {
    $config = new StripeConfig([
        'secret_key' => 'sk_test_stripe_secret',
        'webhook_secret' => 'whsec_stripe_webhook_secret',
    ]);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_STRIPE_URL_MISSING);

    StripeTraitStub::getStripeUrl($config, new Collection([]));
});

it('verify stripe webhook sign empty signature', function () {
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/stripe/notify', [], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    StripeTraitStub::verifyStripeWebhookSign($request, []);
});

it('verify stripe webhook sign missing webhook secret throws exception', function () {
    $request = new ServerRequest('POST', 'https://example.com', [], '{}');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_STRIPE_INVALID);

    StripeTraitStub::verifyStripeWebhookSign($request, ['_config' => 'no_webhook_secret']);
});

it('verify stripe webhook sign malformed signature header throws exception', function () {
    $request = new ServerRequest('POST', 'https://example.com', [
        'Stripe-Signature' => 'no-equals-sign-here',
    ], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    StripeTraitStub::verifyStripeWebhookSign($request, []);
});

it('verify stripe webhook sign expired timestamp throws exception', function () {
    $oldTimestamp = time() - 400;
    $signatureHeader = 't='.$oldTimestamp.',v1=fakesig';

    $request = new ServerRequest('POST', 'https://example.com', [
        'Stripe-Signature' => $signatureHeader,
    ], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    StripeTraitStub::verifyStripeWebhookSign($request, []);
});

it('verify stripe webhook sign wrong signature throws exception', function () {
    $timestamp = time();
    $signatureHeader = 't='.$timestamp.',v1=invalidsignaturevalue';

    $request = new ServerRequest('POST', 'https://example.com', [
        'Stripe-Signature' => $signatureHeader,
    ], '{"id":"evt_test"}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    StripeTraitStub::verifyStripeWebhookSign($request, []);
});

it('verify stripe webhook sign valid signature passes', function () {
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

    expect(true)->toBeTrue();
});

it('verify stripe webhook sign multiple signatures passes', function () {
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

    expect(true)->toBeTrue();
});

