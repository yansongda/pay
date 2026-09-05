<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception as PayException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DouyinTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::douyin()->foo();
    }

    public function testCallMini()
    {
        $fields = [
            'outOrderNo' => '20260905123456',
            'totalAmount' => 100,
            'skuList' => [
                ['skuId' => 'sku_001', 'count' => 1, 'price' => 100],
            ],
            'orderEntrySchema' => ['path' => 'pages/index/index'],
        ];

        $result = Pay::douyin()->mini($fields);

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame(['data', 'byteAuthorization'], array_keys($result->all()));

        $data = $result->get('data');
        $byteAuthorization = $result->get('byteAuthorization');
        self::assertIsString($data);
        self::assertIsString($byteAuthorization);
        self::assertSame($fields, json_decode($data, true));

        // 反解 byteAuthorization 中的 nonce_str/timestamp/signature，用 app 公钥复算验签（端到端兜底）
        self::assertSame(1, preg_match(
            '/nonce_str="([^"]+)",timestamp="([^"]+)",key_version=1,signature="([^"]+)"/',
            $byteAuthorization,
            $matches
        ));

        [, $nonce, $timestamp, $signature] = $matches;

        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../Cert/douyinAppPublicKey.pem'));
        self::assertNotFalse($publicKey);
        self::assertSame(1, openssl_verify(
            "POST\n/requestOrder\n".$timestamp."\n".$nonce."\n".$data."\n",
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ));
    }

    public function testCancel()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(PayException::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::douyin()->cancel([]);
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(PayException::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::douyin()->close([]);
    }

    public function testQuery()
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->twice()->andReturn(
            new Response(200, [], '{"data":{"access_token":"client_token_test","expires_in":7200,"error_code":0,"description":"success"}}'),
            new Response(200, [], '{"err_no":0,"err_tips":"success","data":{"order_id":"7398075047971440922","out_order_no":"20260905123456"}}'),
        );
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::douyin()->query([
            'out_order_no' => '20260905123456',
            '_return_rocket' => true,
        ]);

        self::assertInstanceOf(Rocket::class, $result);

        $destination = $result->getDestination();
        $payload = $result->getPayload();

        self::assertInstanceOf(Collection::class, $destination);
        self::assertSame('7398075047971440922', $destination->get('data.order_id'));
        self::assertSame('20260905123456', $destination->get('data.out_order_no'));
        self::assertSame(
            ['out_order_no', '_return_rocket', '_access_token', '_method', '_url', '_body'],
            array_keys($payload->all())
        );
        self::assertSame('client_token_test', $payload->get('_access_token'));

        Mockery::close();
    }

    public function testRefund()
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->twice()->andReturn(
            new Response(200, [], '{"data":{"access_token":"client_token_test","expires_in":7200,"error_code":0,"description":"success"}}'),
            new Response(200, [], '{"err_no":0,"err_tips":"受理成功","data":{"refund_id":"7398108028894988571"}}'),
        );
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::douyin()->refund([
            'order_id' => '7398075047971440922',
            'out_refund_no' => '20260905123456',
            'refund_reason' => '测试退款',
            'refund_amount' => 1,
            '_return_rocket' => true,
        ]);

        self::assertInstanceOf(Rocket::class, $result);

        $destination = $result->getDestination();
        $payload = $result->getPayload();

        self::assertInstanceOf(Collection::class, $destination);
        self::assertSame('7398108028894988571', $destination->get('data.refund_id'));
        self::assertSame(
            ['order_id', 'out_refund_no', 'refund_reason', 'refund_amount', '_return_rocket', '_access_token', '_method', '_url', 'notify_url', '_body'],
            array_keys($payload->all())
        );
        self::assertSame('https://yansongda.cn/douyin/notify', $payload->get('notify_url'));

        Mockery::close();
    }

    public function testCallback()
    {
        $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda","total_amount":1,"status":"SUCCESS","seller_uid":"73744242495132490630","paid_at":1722769986}';
        $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $result = Pay::douyin()->callback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame('7398108028895054107', $result->get('order_id'));
        self::assertSame('yansongda', $result->get('out_trade_no'));
        self::assertSame(1, $result->get('total_amount'));
        self::assertSame('SUCCESS', $result->get('status'));
    }

    public function testCallbackWithArrayContents()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(PayException::PARAMS_CALLBACK_REQUEST_INVALID);

        Pay::douyin()->callback(['type' => 'payment', 'msg' => '{}']);
    }

    public function testCallbackTamperedSignature()
    {
        $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda"}';
        $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(PayException::SIGN_ERROR);

        Pay::douyin()->callback($request);
    }

    public function testRefundCallback()
    {
        $msg = '{"refund_id":"7398108028895054107","out_refund_no":"yansongda-refund","refund_amount":1,"status":"SUCCESS"}';
        $body = '{"version":"3.0","type":"refund","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $result = Pay::douyin()->refundCallback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame('7398108028895054107', $result->get('refund_id'));
        self::assertSame('yansongda-refund', $result->get('out_refund_no'));
        self::assertSame('SUCCESS', $result->get('status'));
    }

    public function testPreRefundCallback()
    {
        $msg = '{"order_id":"7398108028895054107","out_refund_no":"yansongda-refund","refund_amount":1,"reason":"测试退款"}';
        $body = '{"version":"3.0","type":"pre_create_refund","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $result = Pay::douyin()->preRefundCallback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame('7398108028895054107', $result->get('order_id'));
        self::assertSame('yansongda-refund', $result->get('out_refund_no'));
        self::assertSame('测试退款', $result->get('reason'));
    }

    public function testSuccess()
    {
        $result = Pay::douyin()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame('{"err_no":0,"err_tips":"success"}', (string) $result->getBody());
    }

    /**
     * @param array<string, string> $overrideHeaders
     */
    private function getDouyinCallbackRequest(string $body, array $overrideHeaders = []): ServerRequest
    {
        $headers = [
            'Byte-Timestamp' => '1700000000',
            'Byte-Nonce-Str' => 'abcdef1234567890',
            'Byte-Signature' => base64_encode($this->signDouyinContents("1700000000\nabcdef1234567890\n".$body."\n")),
        ];

        return new ServerRequest('POST', 'https://yansongda.cn/douyin/notify', array_merge($headers, $overrideHeaders), $body);
    }

    private function signDouyinContents(string $contents): string
    {
        $privateKey = openssl_pkey_get_private(file_get_contents(__DIR__.'/../Cert/douyinPlatformPrivateKey.pem'));
        self::assertNotFalse($privateKey);

        self::assertTrue(openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256));

        return $signature;
    }
}
