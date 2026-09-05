<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Pay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
    private CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testPaymentCallback(): void
    {
        $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda","total_amount":1,"status":"SUCCESS","seller_uid":"73744242495132490630","paid_at":1722769986}';
        $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        $expected = new Collection(json_decode($msg, true));

        self::assertSame($expected->all(), $result->getPayload()->all());
        self::assertSame($expected->all(), $result->getDestination()->all());
        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());
    }

    public function testPaymentCallbackTamperedSignature(): void
    {
        $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda","total_amount":1,"status":"SUCCESS"}';
        $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')]);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testPaymentCallbackWithRefundType(): void
    {
        $msg = '{"refund_order_id":"7398108028895054107","out_refund_no":"yansongda-refund"}';
        $body = '{"version":"3.0","type":"refund","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testPaymentCallbackWithoutRequest(): void
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
    private function getDouyinCallbackRequest(string $body, array $overrideHeaders = []): ServerRequest
    {
        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Logid' => '202408041111312119',
            'Byte-Identifyname' => 'trade',
            'Byte-Signature' => base64_encode(
                $this->signDouyinContents("1700000000\nabcdef1234567890\n".$body."\n")
            ),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signDouyinContents(string $contents): string
    {
        $privateKey = openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../../Cert/douyinPlatformPrivateKey.pem'));
        self::assertNotFalse($privateKey);

        self::assertTrue(openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256));

        return $signature;
    }
}
