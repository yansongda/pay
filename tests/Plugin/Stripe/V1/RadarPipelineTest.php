<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CancelPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\WebPlugin;
use Yansongda\Pay\Tests\Support\Traits\StripeRadarPipelineHelpers;

/**
 * 全管道（StartPlugin → 业务插件 → AddRadarPlugin）级别断言。
 *
 * 重点验证最终发出的 HTTP 请求（URI/query string/body）不残留内部参数，
 * 防止 `payment_intent_id`/`refund_id` 等参数泄漏导致 Stripe 返回 `parameter_unknown` 错误。
 */
uses(StripeRadarPipelineHelpers::class);

it('查询 payment intent 不泄漏内部参数', function () {
    $rocket = $this->runPipeline(['payment_intent_id' => 'pi_3PqXXX'], [QueryPlugin::class]);
    $radar = $rocket->getRadar();
    $uri = (string) $radar->getUri();

    expect($radar->getMethod())->toEqual('GET');
    expect($uri)->toEqual('https://api.stripe.com/v1/payment_intents/pi_3PqXXX');
    expect($uri)->not->toContain('payment_intent_id');
});

it('撤销不泄漏内部参数', function () {
    $rocket = $this->runPipeline(
        ['payment_intent_id' => 'pi_3PqXXX', 'cancellation_reason' => 'duplicate'],
        [CancelPlugin::class]
    );
    $radar = $rocket->getRadar();
    $uri = (string) $radar->getUri();
    $body = (string) $radar->getBody();

    expect($radar->getMethod())->toEqual('POST');
    expect($uri)->toEqual('https://api.stripe.com/v1/payment_intents/pi_3PqXXX/cancel');
    expect($body)->toEqual('cancellation_reason=duplicate');
    expect($uri.$body)->not->toContain('payment_intent_id');
});

it('查询退款不泄漏内部参数', function () {
    $rocket = $this->runPipeline(['refund_id' => 're_6PqXXX'], [QueryRefundPlugin::class]);
    $radar = $rocket->getRadar();
    $uri = (string) $radar->getUri();

    expect($radar->getMethod())->toEqual('GET');
    expect($uri)->toEqual('https://api.stripe.com/v1/refunds/re_6PqXXX');
    expect($uri)->not->toContain('refund_id');
});

it('退款保留业务参数', function () {
    $rocket = $this->runPipeline(['payment_intent' => 'pi_3PqXXX', 'amount' => 100], [RefundPlugin::class]);
    $radar = $rocket->getRadar();
    $body = (string) $radar->getBody();

    // `payment_intent` 是 Refund API 的合法参数，应保留在 body 中
    expect((string) $radar->getUri())->toEqual('https://api.stripe.com/v1/refunds');
    expect($body)->toContain('payment_intent=pi_3PqXXX');
    expect($body)->toContain('amount=100');
});

it('支付 body 仅包含必需参数', function () {
    $rocket = $this->runPipeline(['amount' => 1000, 'currency' => 'usd'], [PayPlugin::class]);
    $radar = $rocket->getRadar();
    $body = (string) $radar->getBody();

    expect((string) $radar->getUri())->toEqual('https://api.stripe.com/v1/payment_intents');
    expect($body)->toContain('amount=1000');
    expect($body)->toContain('currency=usd');
    expect($body)->not->toContain('_method');
    expect($body)->not->toContain('_url');
});

it('网页 body 包含收银台参数', function () {
    $rocket = $this->runPipeline(
        ['line_items' => [['price' => 'price_xxx', 'quantity' => 1]]],
        [WebPlugin::class]
    );
    $radar = $rocket->getRadar();
    $body = (string) $radar->getBody();

    expect((string) $radar->getUri())->toEqual('https://api.stripe.com/v1/checkout/sessions');
    expect($body)->toContain('mode=payment');
    expect($body)->toContain('success_url');
    expect($body)->not->toContain('_config');
});

it('支付缺少金额抛出异常', function () {

    $this->runPipeline(['currency' => 'usd'], [PayPlugin::class]);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('网页支付缺少 success_url 抛出异常', function () {

    $this->runPipeline(['_config' => 'no_success_url'], [WebPlugin::class]);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);
