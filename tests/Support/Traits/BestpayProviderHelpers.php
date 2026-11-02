<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Pay;

/**
 * 翼支付 Provider Helper。
 *
 * mock HTTP 客户端，响应体按翼支付规则签名，供 Provider 全链路用例使用。
 */
trait BestpayProviderHelpers
{
    /**
     * @param array<string, mixed> $body
     */
    private function mockResponse(array $body): void
    {
        $body['sign'] = $this->signResponseBody($body);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($body)));
        Pay::set(HttpClientInterface::class, $http);
    }

    /**
     * @param string              $expectedBodyFragment 断言请求 body 包含的片段
     * @param array<string, mixed> $body
     */
    private function mockResponseWithUriCheck(string $expectedBodyFragment, array $body): void
    {
        $body['sign'] = $this->signResponseBody($body);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturnUsing(function ($request) use ($body, $expectedBodyFragment) {
            expect((string) $request->getBody())->toContain($expectedBodyFragment);

            return new Response(200, [], json_encode($body));
        });
        Pay::set(HttpClientInterface::class, $http);
    }

    private function signResponseBody(array $body): string
    {
        return self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($body));
    }

    private function defaultConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => cert_path('bestpay/bestpay.p12'),
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => cert_path('bestpay/bestpay.cer'),
        ]);
    }
}
