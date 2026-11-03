<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use Yansongda\Pay\Tests\Support\Traits\AlipayProviderHelpers;

uses(AlipayProviderHelpers::class);

describe('shortcut', function () {
    it('不存在', function () {

        Pay::alipay()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);

    it('不兼容', function () {

        Pay::alipay()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);
});

describe('pay', function () {
    it('网页', function () {
        $result = Pay::alipay()->web([
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.page.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22product_code%22%3A%22FAST_INSTANT_TRADE_PAY%22%2C%22out_trade_no';
        $body3 = 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        expect((string) $radar->getBody())->toContain($body1);
        expect((string) $radar->getBody())->toContain($body2);
        expect((string) $radar->getBody())->toContain($body3);
        expect($radar->getMethod())->toEqual('POST');
    });

    it('网页 GET', function () {
        $result = Pay::alipay()->web([
            '_method' => 'get',
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.page.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22product_code%22%3A%22FAST_INSTANT_TRADE_PAY%22%2C%22out_trade_no';
        $body3 = 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        expect((string) $radar->getBody())->toContain($body1);
        expect((string) $radar->getBody())->toContain($body2);
        expect((string) $radar->getBody())->toContain($body3);
        expect($radar->getMethod())->toEqual('GET');
    });

    it('H5', function () {
        $result = Pay::alipay()->h5([
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            'quit_url' => 'https://yansongda.cn',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.wap.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no%22%3A%22web';
        $body3 = 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%2C%22quit_url%22%3A%22https%3A%5C%2F%5C%2Fyansongda.cn%22%7D&sign=';

        expect((string) $radar->getBody())->toContain($body1);
        expect((string) $radar->getBody())->toContain($body2);
        expect((string) $radar->getBody())->toContain($body3);
        expect($radar->getMethod())->toEqual('POST');
    });

    it('H5 GET', function () {
        $result = Pay::alipay()->h5([
            '_method' => 'get',
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
            'quit_url' => 'https://yansongda.cn',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.wap.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no%22%3A%22web';
        $body3 = 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%2C%22quit_url%22%3A%22https%3A%5C%2F%5C%2Fyansongda.cn%22%7D&sign=';

        expect((string) $radar->getBody())->toContain($body1);
        expect((string) $radar->getBody())->toContain($body2);
        expect((string) $radar->getBody())->toContain($body3);
        expect($radar->getMethod())->toEqual('GET');
    });

    it('APP', function () {
        $result = Pay::alipay()->app([
            'out_trade_no' => 'web'.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 01',
        ]);

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.trade.app.pay&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22out_trade_no%22%3A%22web';
        $body3 = 'total_amount%22%3A%220.01%22%2C%22subject%22%3A%22yansongda+%5Cu6d4b%5Cu8bd5+-+01%22%7D&sign=';

        expect((string) $result->getBody())->toContain($body1);
        expect((string) $result->getBody())->toContain($body2);
        expect((string) $result->getBody())->toContain($body3);
    });

    it('转账', function () {
        $response = [
            'alipay_fund_trans_uni_transfer_response' => [
                'code' => '10000',
                'msg' => 'Success',
                'order_id' => '20231226110070000002150000683137',
                'out_biz_no' => '2023122621450001',
                'pay_fund_order_id' => '20231226110070001502150000685481',
                'status' => 'SUCCESS',
                'trans_date' => '2023-12-26 22:11:45',
            ],
            'sign' => 'exg0CUSgsRvI+q/Qqyu+MJ17ao4+vnEUMRE4YNbN2H3K6iX3xBcZv9jTt6m6c9JLZIifbqkZU13PLa4zy1MaQnQKg676wbqpN7ybEVL7LMzAgXUFm3Dc0XL1minPie2XOtwIgEecoPwpEqvqjjdTXfaE7fT6ZLxFLMMlPAESGwDDnKQVUmWs/8oq/EdPDNtVMmoVbF4o9zizyHw/QHVpLYvt0DHNCZRLhY85V99W6CrHjkNTB1QzEb1vCe3okVT3UAq26sxpu46R5l3n0xKJiYrucs8Y6CEWmayTKmZou7WQdgKQJHC0x0OIN58zWBkAFwz9ZAGON/WO3YHWq6mi5A==',
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->transfer([
            'out_biz_no' => '2023122621450001',
            'trans_amount' => '0.01',
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'DIRECT_TRANSFER',
            'payee_info' => [
                'identity' => 'ifvlwp1413@sandbox.com',
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name' => 'ifvlwp1413',
            ],
            '_return_rocket' => true,
        ]);

        // 支付宝参数里有实时时间，导致签名不一样，这里只验证签名之前的部分
        $body1 = 'app_id=9021000122682882&method=alipay.fund.trans.uni.transfer&format=JSON&return_url=https%3A%2F%2Fpay.yansongda.cn&charset=utf-8&sign_type=RSA2&timestamp=';
        $body2 = '&version=1.0&notify_url=https%3A%2F%2Fpay.yansongda.cn&app_cert_sn=e90dd23a37c5c7b616e003970817ff82&alipay_root_cert_sn=687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6&biz_content=%7B%22biz_scene%22%3A%22DIRECT_TRANSFER%22%2C%22product_code%22%3A%22TRANS_ACCOUNT_NO_PWD%22%2C%22out_biz_no%22%3A%222023122621450001%22%2C%22trans_amount%22%3A%220.01%22%2C%22payee_info%22%3A%7B%22identity%22%3A%22ifvlwp1413%40sandbox.com%22%2C%22identity_type%22%3A%22ALIPAY_LOGON_ID%22%2C%22name%22%3A%22ifvlwp1413%22%7D%7D&sign=';

        expect((string) $result->getRadar()->getBody())->toContain($body1);
        expect((string) $result->getRadar()->getBody())->toContain($body2);
        expect($result->getDestination()->except('_sign')->all())->toEqualCanonicalizing($response['alipay_fund_trans_uni_transfer_response']);
    });
});

describe('callback', function () {
    it('正常', function () {
        $url = 'http://127.0.0.1:8000/alipay/verify?charset=utf-8&out_trade_no=1703141270&method=alipay.trade.page.pay.return&total_amount=0.01&sign=RJzbs5y7I41BO9UPnCdq7oWgoInyjELi9Qj6D%2BLAZXVpHTedemAHfVUowuF9iuznGZLxU6Xv1L3ZkzTGxmIfvzontCZNb0%2BRROqiT41lX91VYd6j4ZcOn8zsvlCdQSVHmYNJi%2Bw%2F40uHxo1ufRwHxBNtQKsoJCYk5VtZ92pQFvVyE5wPPT6Nolww5WlCAPxcWNby8VAiWT%2Bd2yxmFm8vZ6yj5rsLHTR72O76TkEXzOEex6e36Zf8M9YXww7RQbflMfk9eURPHW%2FoQq4hZr%2FlX7%2FO1nT5vdT4UVFai4V18Xm1KspBun8outJxqlWMIKVxGsYhIH1E79ORt4wQA7PG1g%3D%3D&trade_no=2023122122001499160501586202&auth_app_id=9021000122682882&version=1.0&app_id=9021000122682882&sign_type=RSA2&seller_id=2088721003899159&timestamp=2023-12-21+14%3A48%3A44';
        parse_str(parse_url($url)['query'], $query);
        $request = new ServerRequest('GET', $url);
        $request = $request->withQueryParams($query);

        $result = Pay::alipay()->callback($request);
        expect($result->all())->not->toBeEmpty();

        $result = Pay::alipay()->callback($query);
        expect($result->all())->not->toBeEmpty();
    });

    it('应用回调', function () {
        $appCallbackParams = [
            'alipay_trade_app_pay_response' => [
                'code' => '10000',
                'msg' => 'Success',
                'order_id' => '20231220110070000002150000657610',
                'out_biz_no' => '2023122022560000',
                'pay_date' => '2023-12-20 22:56:33',
                'pay_fund_order_id' => '20231220110070001502150000660902',
                'status' => 'SUCCESS',
                'trans_amount' => '0.01',
            ],
            'sign' => 'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A==',
            'sign_type' => 'RSA2',
        ];

        $url = 'http://127.0.0.1:8000/alipay/app_verify';
        $request = new ServerRequest('POST', $url);
        $request = $request->withParsedBody($appCallbackParams);

        $result = Pay::alipay()->appCallback($request);
        expect($result->all())->not->toBeEmpty();

        $result = Pay::alipay()->appCallback($appCallbackParams, ['_config' => 'default']);
        expect($result->all())->not->toBeEmpty();

        Pay::alipay()->appCallback([]);
    })->throws(InvalidParamsException::class, null, \Yansongda\Pay\Exception\Exception::PARAMS_CALLBACK_REQUEST_INVALID);
});

describe('success', function () {
    it('正常', function () {
        $result = Pay::alipay()->success();

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect((string) $result->getBody())->toEqual('success');
    });
});

describe('V3', function () {
    it('扫码', function () {
        // V3 scan 正向链路：mock HTTP 返回带 V3 header 签名的响应，全链路（预下单 → 验签 → 解析）断言。
        $responseData = [
            'out_trade_no' => 'v3scan1704093802',
            'qr_code' => 'https://qr.alipay.com/bax07651xvtprxfkmxyf00a9',
        ];
        $body = json_encode($responseData);
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';

        openssl_sign($timestamp."\n".$nonce."\n".$body."\n", $sign, openssl_pkey_get_private(file_get_contents(cert_path('alipay-v3/app_secret_test.pem'))), OPENSSL_ALGO_SHA256);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
            'alipay-sn' => CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt')),
        ], $body));
        Pay::set(HttpClientInterface::class, $http);

        $result = Artful::artful((new \Yansongda\Pay\Shortcut\Alipay\V3\ScanShortcut())->getPlugins([]), [
            '_config' => 'alipay-v3',
            'out_trade_no' => 'v3scan1704093802',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        expect($radar->getMethod())->toEqual('POST');
        expect((string) $radar->getUri())->toEqual('https://openapi.alipay.com/v3/alipay/trade/precreate');

        $requestBody = json_decode((string) $radar->getBody(), true);
        expect($requestBody['out_trade_no'])->toBe('v3scan1704093802');
        expect($requestBody['total_amount'])->toBe('0.01');
        expect($requestBody['subject'])->toBe('yansongda 测试 - V3');

        expect($result->getDestination()->all())->toEqualCanonicalizing($responseData);
    });

    it('回调（ServerRequest）', function () {
        $form = $this->makeV3CallbackForm();

        $request = (new ServerRequest('POST', 'https://pay.yansongda.cn/alipay/notify'))->withParsedBody($form);

        $result = Pay::alipay()->callback($request, ['_config' => 'alipay-v3']);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->except('_config')->all())->toBe($form);
        expect($result->get('trade_status'))->toBe('TRADE_SUCCESS');
    });

    it('回调（纯数组）', function () {
        // 普通数组（通知 form 参数）构造为 parsedBody 的模拟回调请求
        $form = $this->makeV3CallbackForm();

        $result = Pay::alipay()->callback($form, ['_config' => 'alipay-v3']);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->except('_config')->all())->toBe($form);
        expect($result->get('trade_status'))->toBe('TRADE_SUCCESS');
    });

    it('回调（body + headers）', function () {
        $form = $this->makeV3CallbackForm();

        $result = Pay::alipay()->callback(
            ['body' => http_build_query($form), 'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']],
            ['_config' => 'alipay-v3']
        );

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->except('_config')->all())->toBe($form);
    });

    it('回调参数被篡改', function () {
        $form = $this->makeV3CallbackForm();
        $form['total_amount'] = '999.00';

        Pay::alipay()->callback($form, ['_config' => 'alipay-v3']);
    })->throws(InvalidSignException::class);
});

describe('gateway', function () {
    it('正常', function () {
        // 拍板入口形态：`_action` 位于第一参数 contents
        $form = $this->makeGwVerifyForm();

        $result = Pay::alipay()->callback(array_merge($form, ['_action' => 'gw']), ['_config' => 'alipay-v3']);

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect($result->getStatusCode())->toBe(200);
        expect((string) $result->getBody())->toContain('<success>true</success>');
        expect((string) $result->getBody())->toContain('<sign_type>RSA2</sign_type>');
    });

    it('webhook 表单方式', function () {
        // webhook 转发形态：`body`/`headers` 结构，`_action` 位于第二实参
        $form = $this->makeGwVerifyForm();

        $result = Pay::alipay()->callback(
            ['body' => http_build_query($form), 'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']],
            ['_config' => 'alipay-v3', '_action' => 'gw']
        );

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect($result->getStatusCode())->toBe(200);
        expect((string) $result->getBody())->toContain('<success>true</success>');
    });

    it('未知动作', function () {

        Pay::alipay()->callback(['_action' => 'foo']);
    })->throws(InvalidParamsException::class, '参数异常: 不支持的回调 _action [foo]', \Yansongda\Pay\Exception\Exception::PARAMS_SHORTCUT_ACTION_INVALID);
});