<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Stubs\StripeTraitStub;

it('获取 Stripe URL', function () {
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

it('获取 Stripe URL 缺少参数抛出异常', function () {
    $config = new StripeConfig([
        'secret_key' => 'sk_test_stripe_secret',
        'webhook_secret' => 'whsec_stripe_webhook_secret',
    ]);

    StripeTraitStub::getStripeUrl($config, new Collection([]));
})->throws(InvalidParamsException::class, null, Exception::PARAMS_STRIPE_URL_MISSING);

it('Stripe webhook 验签：签名为空', function () {
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/stripe/notify', [], '{}');

    StripeTraitStub::verifyStripeWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

it('Stripe webhook 验签：缺少密钥抛出异常', function () {
    $request = new ServerRequest('POST', 'https://example.com', [], '{}');

    StripeTraitStub::verifyStripeWebhookSign($request, ['_config' => 'no_webhook_secret']);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_STRIPE_INVALID);

it('Stripe webhook 验签：签名头格式错误抛出异常', function () {
    $request = new ServerRequest('POST', 'https://example.com', [
        'Stripe-Signature' => 'no-equals-sign-here',
    ], '{}');

    StripeTraitStub::verifyStripeWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('Stripe webhook 验签：时间戳过期抛出异常', function () {
    $oldTimestamp = time() - 400;
    $signatureHeader = 't='.$oldTimestamp.',v1=fakesig';

    $request = new ServerRequest('POST', 'https://example.com', [
        'Stripe-Signature' => $signatureHeader,
    ], '{}');

    StripeTraitStub::verifyStripeWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('Stripe webhook 验签：签名错误抛出异常', function () {
    $timestamp = time();
    $signatureHeader = 't='.$timestamp.',v1=invalidsignaturevalue';

    $request = new ServerRequest('POST', 'https://example.com', [
        'Stripe-Signature' => $signatureHeader,
    ], '{"id":"evt_test"}');

    StripeTraitStub::verifyStripeWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('Stripe webhook 验签：签名有效通过', function () {
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

it('Stripe webhook 验签：多签名通过', function () {
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

