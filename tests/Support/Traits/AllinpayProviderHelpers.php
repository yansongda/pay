<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\Stubs\AllinpayTraitHelper;

/**
 * 通联支付 Provider Helper。
 *
 * mock HTTP 客户端并捕获请求，响应体用平台私钥签名（RSA-SHA1），供 Provider 全链路用例使用。
 */
trait AllinpayProviderHelpers
{
    private ?RequestInterface $capturedRequest = null;

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     */
    private function signedResponse(array $data): array
    {
        $data = array_filter($data, static fn ($v) => null !== $v);
        $content = AllinpayTraitHelper::getAllinpaySignContent($data);

        $sign = '';
        openssl_sign($content, $sign, file_get_contents(cert_path('allinpayPlatformPrivateKey.pem')), OPENSSL_ALGO_SHA1);

        $data['sign'] = base64_encode($sign);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mockHttp(array $data): void
    {
        $this->capturedRequest = null;

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturnUsing(function (RequestInterface $request) use ($data) {
            $this->capturedRequest = $request;

            return new Response(200, ['Content-Type' => 'application/json'], json_encode($data));
        });
        Pay::set(HttpClientInterface::class, $http);
    }

    /**
     * @return array<string, mixed>
     */
    private function capturedBody(): array
    {
        $fields = [];
        parse_str((string) ($this->capturedRequest?->getBody() ?? ''), $fields);

        return $fields;
    }
}
