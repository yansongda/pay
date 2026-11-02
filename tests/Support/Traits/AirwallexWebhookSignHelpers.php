<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Pay\Traits\AirwallexTrait;

/**
 * Airwallex Webhook 验签 Helper。
 *
 * 通过匿名类引入 AirwallexTrait，直接调用静态验签方法（无需构造 Provider）。
 */
trait AirwallexWebhookSignHelpers
{
    protected static function verifyWebhookSign(ServerRequest $request, array $params = []): void
    {
        $verifier = new class() {
            use AirwallexTrait;
        };

        $verifier::verifyAirwallexWebhookSign($request, $params);
    }
}
