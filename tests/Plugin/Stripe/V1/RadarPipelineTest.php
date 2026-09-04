<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CancelPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\WebPlugin;
use Yansongda\Pay\Tests\TestCase;

/**
 * 全管道（StartPlugin → 业务插件 → AddRadarPlugin）级别断言。
 *
 * 重点验证最终发出的 HTTP 请求（URI/query string/body）不残留内部参数，
 * 防止 `payment_intent_id`/`refund_id` 等参数泄漏导致 Stripe 返回 `parameter_unknown` 错误。
 */
class RadarPipelineTest extends TestCase
{
    /**
     * @param array<string, mixed> $params
     * @param array<class-string>  $businessPlugins
     *
     * @throws InvalidParamsException
     */
    protected function runPipeline(array $params, array $businessPlugins): Rocket
    {
        $rocket = new Rocket();
        $rocket->setParams($params);

        foreach ([StartPlugin::class, ...$businessPlugins, AddRadarPlugin::class] as $plugin) {
            $rocket = (new $plugin())->assembly($rocket, static fn ($rocket) => $rocket);
        }

        return $rocket;
    }

    public function testQueryPaymentIntentDoesNotLeakInternalParams()
    {
        $rocket = $this->runPipeline(['payment_intent_id' => 'pi_3PqXXX'], [QueryPlugin::class]);
        $radar = $rocket->getRadar();
        $uri = (string) $radar->getUri();

        self::assertEquals('GET', $radar->getMethod());
        self::assertEquals('https://api.stripe.com/v1/payment_intents/pi_3PqXXX', $uri);
        self::assertStringNotContainsString('payment_intent_id', $uri);
    }

    public function testCancelDoesNotLeakInternalParams()
    {
        $rocket = $this->runPipeline(
            ['payment_intent_id' => 'pi_3PqXXX', 'cancellation_reason' => 'duplicate'],
            [CancelPlugin::class]
        );
        $radar = $rocket->getRadar();
        $uri = (string) $radar->getUri();
        $body = (string) $radar->getBody();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://api.stripe.com/v1/payment_intents/pi_3PqXXX/cancel', $uri);
        self::assertEquals('cancellation_reason=duplicate', $body);
        self::assertStringNotContainsString('payment_intent_id', $uri.$body);
    }

    public function testQueryRefundDoesNotLeakInternalParams()
    {
        $rocket = $this->runPipeline(['refund_id' => 're_6PqXXX'], [QueryRefundPlugin::class]);
        $radar = $rocket->getRadar();
        $uri = (string) $radar->getUri();

        self::assertEquals('GET', $radar->getMethod());
        self::assertEquals('https://api.stripe.com/v1/refunds/re_6PqXXX', $uri);
        self::assertStringNotContainsString('refund_id', $uri);
    }

    public function testRefundKeepsBusinessParams()
    {
        $rocket = $this->runPipeline(['payment_intent' => 'pi_3PqXXX', 'amount' => 100], [RefundPlugin::class]);
        $radar = $rocket->getRadar();
        $body = (string) $radar->getBody();

        // `payment_intent` 是 Refund API 的合法参数，应保留在 body 中
        self::assertEquals('https://api.stripe.com/v1/refunds', (string) $radar->getUri());
        self::assertStringContainsString('payment_intent=pi_3PqXXX', $body);
        self::assertStringContainsString('amount=100', $body);
    }

    public function testPayBodyContainsRequiredParamsOnly()
    {
        $rocket = $this->runPipeline(['amount' => 1000, 'currency' => 'usd'], [PayPlugin::class]);
        $radar = $rocket->getRadar();
        $body = (string) $radar->getBody();

        self::assertEquals('https://api.stripe.com/v1/payment_intents', (string) $radar->getUri());
        self::assertStringContainsString('amount=1000', $body);
        self::assertStringContainsString('currency=usd', $body);
        self::assertStringNotContainsString('_method', $body);
        self::assertStringNotContainsString('_url', $body);
    }

    public function testWebBodyContainsCheckoutParams()
    {
        $rocket = $this->runPipeline(
            ['line_items' => [['price' => 'price_xxx', 'quantity' => 1]]],
            [WebPlugin::class]
        );
        $radar = $rocket->getRadar();
        $body = (string) $radar->getBody();

        self::assertEquals('https://api.stripe.com/v1/checkout/sessions', (string) $radar->getUri());
        self::assertStringContainsString('mode=payment', $body);
        self::assertStringContainsString('success_url', $body);
        self::assertStringNotContainsString('_config', $body);
    }

    public function testPayMissingAmountThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->runPipeline(['currency' => 'usd'], [PayPlugin::class]);
    }

    public function testWebMissingSuccessUrlThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->runPipeline(['_config' => 'no_success_url'], [WebPlugin::class]);
    }
}
