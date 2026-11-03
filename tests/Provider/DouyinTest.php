<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception as PayException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\DouyinHelpers;

uses(DouyinHelpers::class);

describe('shortcut', function () {
    it('不存在', function () {

        Pay::douyin()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);
});

describe('pay', function () {
    it('调用小程序', function () {
        $fields = [
            'outOrderNo' => '20260905123456',
            'totalAmount' => 100,
            'skuList' => [
                ['skuId' => 'sku_001', 'count' => 1, 'price' => 100],
            ],
            'orderEntrySchema' => ['path' => 'pages/index/index'],
        ];

        $result = Pay::douyin()->mini($fields);

        expect($result)->toBeInstanceOf(Collection::class);
        expect(array_keys($result->all()))->toBe(['data', 'byteAuthorization']);

        $data = $result->get('data');
        $byteAuthorization = $result->get('byteAuthorization');
        expect($data)->toBeString();
        expect($byteAuthorization)->toBeString();
        expect(json_decode($data, true))->toBe($fields);

        // 反解 byteAuthorization 中的 nonce_str/timestamp/signature，用 app 公钥复算验签（端到端兜底）
        expect(preg_match(
            '/nonce_str="([^"]+)",timestamp="([^"]+)",key_version=1,signature="([^"]+)"/',
            $byteAuthorization,
            $matches
        ))->toBe(1);

        [, $nonce, $timestamp, $signature] = $matches;

        $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('douyinAppPublicKey.pem')));
        expect($publicKey)->not->toBeFalse();
        expect(openssl_verify(
            "POST\n/requestOrder\n".$timestamp."\n".$nonce."\n".$data."\n",
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ))->toBe(1);
    });
});

describe('action', function () {
    it('撤销', function () {

        Pay::douyin()->cancel([]);
    })->throws(InvalidParamsException::class, null, PayException::PARAMS_METHOD_NOT_SUPPORTED);

    it('关单', function () {

        Pay::douyin()->close([]);
    })->throws(InvalidParamsException::class, null, PayException::PARAMS_METHOD_NOT_SUPPORTED);

    it('查询', function () {
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

        expect($result)->toBeInstanceOf(Rocket::class);

        $destination = $result->getDestination();
        $payload = $result->getPayload();

        expect($destination)->toBeInstanceOf(Collection::class);
        expect($destination->get('data.order_id'))->toBe('7398075047971440922');
        expect($destination->get('data.out_order_no'))->toBe('20260905123456');
        expect(array_keys($payload->all()))->toBe(['out_order_no', '_return_rocket', '_access_token', '_method', '_url', '_body']);
        expect($payload->get('_access_token'))->toBe('client_token_test');

        Mockery::close();
    });

    it('退款', function () {
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

        expect($result)->toBeInstanceOf(Rocket::class);

        $destination = $result->getDestination();
        $payload = $result->getPayload();

        expect($destination)->toBeInstanceOf(Collection::class);
        expect($destination->get('data.refund_id'))->toBe('7398108028894988571');
        expect(array_keys($payload->all()))->toBe(['order_id', 'out_refund_no', 'refund_reason', 'refund_amount', '_return_rocket', '_access_token', '_method', '_url', 'notify_url', '_body']);
        expect($payload->get('notify_url'))->toBe('https://yansongda.cn/douyin/notify');

        Mockery::close();
    });
});

describe('callback', function () {
    it('正常', function () {
        $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda","total_amount":1,"status":"SUCCESS","seller_uid":"73744242495132490630","paid_at":1722769986}';
        $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $result = Pay::douyin()->callback($request);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->get('order_id'))->toBe('7398108028895054107');
        expect($result->get('out_trade_no'))->toBe('yansongda');
        expect($result->get('total_amount'))->toBe(1);
        expect($result->get('status'))->toBe('SUCCESS');
    });

    it('数组内容', function () {

        Pay::douyin()->callback(['type' => 'payment', 'msg' => '{}']);
    })->throws(InvalidParamsException::class, null, PayException::PARAMS_CALLBACK_REQUEST_INVALID);

    it('签名被篡改', function () {
        $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda"}';
        $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')]);

        Pay::douyin()->callback($request);
    })->throws(InvalidSignException::class, null, PayException::SIGN_ERROR);

    it('退款类型', function () {
        $msg = '{"refund_id":"7398108028895054107","out_refund_no":"yansongda-refund","refund_amount":1,"status":"SUCCESS"}';
        $body = '{"version":"3.0","type":"refund","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $result = Pay::douyin()->callback($request);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->get('refund_id'))->toBe('7398108028895054107');
        expect($result->get('out_refund_no'))->toBe('yansongda-refund');
        expect($result->get('status'))->toBe('SUCCESS');
    });

    it('预创建退款类型', function () {
        $msg = '{"order_id":"7398108028895054107","out_refund_no":"yansongda-refund","refund_amount":1,"reason":"测试退款"}';
        $body = '{"version":"3.0","type":"pre_create_refund","msg":'.json_encode($msg).'}';

        $request = $this->getDouyinCallbackRequest($body);

        $result = Pay::douyin()->callback($request);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->get('order_id'))->toBe('7398108028895054107');
        expect($result->get('out_refund_no'))->toBe('yansongda-refund');
        expect($result->get('reason'))->toBe('测试退款');
    });
});

describe('success', function () {
    it('正常', function () {
        $result = Pay::douyin()->success();

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect((string) $result->getBody())->toBe('{"err_no":0,"err_tips":"success"}');
    });
});