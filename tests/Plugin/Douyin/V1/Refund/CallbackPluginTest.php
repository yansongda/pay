<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Refund;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    private CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testNormal(): void
    {
        $msg = [
            'refund_id' => '7398108028895054107',
            'out_refund_no' => '202408041111312119',
            'out_trade_no' => 'yansongda-trade-001',
            'status' => 'SUCCESS',
            'refund_amount' => 1,
        ];
        $body = json_encode(['version' => '3.0', 'type' => 'refund', 'msg' => json_encode($msg)]);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $this->getCallbackRequest($body)]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($msg, $result->getPayload()->all());
        self::assertSame($msg, $result->getDestination()->all());
        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());
    }

    public function testTamperedSign(): void
    {
        $msg = ['refund_id' => '7398108028895054107', 'out_refund_no' => '202408041111312119'];
        $body = json_encode(['version' => '3.0', 'type' => 'refund', 'msg' => json_encode($msg)]);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $this->getCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')])]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testTypeMismatch(): void
    {
        $msg = ['order_id' => '7398108028895054107', 'out_trade_no' => 'yansongda-trade-001'];
        $body = json_encode(['version' => '3.0', 'type' => 'payment', 'msg' => json_encode($msg)]);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $this->getCallbackRequest($body)]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testMissingRequest(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    /**
     * @param array<string, string> $overrideHeaders
     */
    private function getCallbackRequest(string $body, array $overrideHeaders = [], ?string $signBody = null): ServerRequest
    {
        $signBody = $signBody ?? $body;

        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Signature' => base64_encode(
                $this->signContents("1700000000\nabcdef1234567890\n".$signBody."\n", file_get_contents(__DIR__.'/../../../../Cert/douyinPlatformPrivateKey.pem'))
            ),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signContents(string $contents, string $privateKeyContents): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyContents);
        self::assertNotFalse($privateKey);

        openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signature;
    }
}
