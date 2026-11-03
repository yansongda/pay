<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Allinpay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Allinpay\CallbackPlugin;
use Yansongda\Pay\Tests\Support\Traits\AllinpayProviderHelpers;

uses(AllinpayProviderHelpers::class);

describe('shortcut', function () {
    it('不存在', function () {

        Pay::allinpay()->foo();
    })->throws(InvalidParamsException::class);
});

describe('pay', function () {
    it('正常', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'trxid' => '240101120000000001',
            'reqsn' => 'order-1001',
            'trxstatus' => 'SUCCESS',
            'payinfo' => 'weixin://wxpay/bizpayurl?pr=xxxx',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->unified([
            'reqsn' => 'order-1001',
            'trxamt' => 1,
            'paytype' => 'W02',
            'body' => '测试商品',
        ]);

        expect($result->get('retcode'))->toBe('SUCCESS');
        expect($result->get('payinfo'))->toBe('weixin://wxpay/bizpayurl?pr=xxxx');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('11');
        expect($fields['paytype'] ?? null)->toBe('W02');
        expect($fields['orgid'] ?? null)->toBe('999999');
        expect($fields['notify_url'] ?? null)->toBe('https://pay.yansongda.cn/allinpay/notify');
        expect($fields)->not->toHaveKey('notifyurl');
    });

    it('缺少 reqsn', function () {

        Pay::allinpay()->unified([
            'trxamt' => 1,
        ]);
    })->throws(InvalidParamsException::class);

    it('缺少 trxamt', function () {

        Pay::allinpay()->unified([
            'reqsn' => 'order-1001',
            'paytype' => 'W02',
        ]);
    })->throws(InvalidParamsException::class);

    it('无 payload 签名', function () {

        Pay::allinpay()->pay([AddPayloadSignPlugin::class], []);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    it('签名缺少密钥', function () {
        Pay::config([
            '_force' => true,
            'allinpay' => [
                'default' => [
                    'cusid' => '9900000',
                    'appid' => '000000',
                    'allinpay_public_key' => cert_path('allinpayPlatformPublicKey.pem'),
                    'mode' => PayMode::SANDBOX,
                ],
            ],
        ]);

        Pay::allinpay()->pay([AddPayloadSignPlugin::class], ['reqsn' => 'order-1001']);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_ALLINPAY_INVALID);
});

describe('validation', function () {
    it('扫码缺少参数', function () {

        Pay::allinpay()->scan([
            'reqsn' => 'order-1002',
        ]);
    })->throws(InvalidParamsException::class);

    it('Native 缺少参数', function () {

        Pay::allinpay()->native([
            'reqsn' => 'order-1003',
            'trxamt' => 1,
        ]);
    })->throws(InvalidParamsException::class);

    it('查询缺少参数', function () {

        Pay::allinpay()->query([]);
    })->throws(InvalidParamsException::class);

    it('查询确认缺少参数', function () {

        Pay::allinpay()->queryConfirm([]);
    })->throws(InvalidParamsException::class);

    it('退款缺少参数', function () {

        Pay::allinpay()->refund([
            'oldreqsn' => 'order-1001',
        ]);
    })->throws(InvalidParamsException::class);

    it('撤销缺少参数', function () {

        Pay::allinpay()->cancel([
            'oldtrxid' => '240101120000000001',
        ]);
    })->throws(InvalidParamsException::class);

    it('撤销缺少原订单', function () {

        Pay::allinpay()->cancel([
            'reqsn' => 'cancel-1',
            'trxamt' => 1,
        ]);
    })->throws(InvalidParamsException::class);

    it('关单缺少参数', function () {

        Pay::allinpay()->close([]);
    })->throws(InvalidParamsException::class);

    it('Native 关单缺少参数', function () {

        Pay::allinpay()->nativeClose([]);
    })->throws(InvalidParamsException::class);
});

describe('transport', function () {
    it('HTTP 状态码错误', function () {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(
            new Response(500, ['Content-Type' => 'application/json'], json_encode(['retcode' => 'SUCCESS', 'retmsg' => 'ok']))
        );
        Pay::set(HttpClientInterface::class, $http);

        Pay::allinpay()->query(['reqsn' => 'order-x']);
    })->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

    it('非法请求回调', function () {

        Pay::allinpay()->pay([CallbackPlugin::class], ['_request' => 'invalid', '_params' => []]);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

    it('从全局变量回调', function () {
        $payload = $this->signedResponse([
            'cusid' => '9900000',
            'appid' => '000000',
            'trxid' => '240101120000000001',
            'reqsn' => 'order-1001',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]);

        $backupServer = $_SERVER;
        $backupPost = $_POST;

        try {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = $payload;

            $result = Pay::allinpay()->callback();

            expect($result->get('reqsn'))->toBe('order-1001');
        } finally {
            $_SERVER = $backupServer;
            $_POST = $backupPost;
        }
    });
});

describe('request', function () {
    it('数组参数归一化为 JSON 字符串', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'order-1004',
            'trxstatus' => 'INIT',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->unified([
            'reqsn' => 'order-1004',
            'trxamt' => 1,
            'paytype' => 'W02',
            'extendparams' => ['activity' => 'x1'],
        ]);

        expect($result->get('retcode'))->toBe('SUCCESS');

        $fields = $this->capturedBody();

        expect($fields['extendparams'] ?? null)->toBe('{"activity":"x1"}');
    });

    it('扫码', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'order-1002',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->scan([
            'reqsn' => 'order-1002',
            'trxamt' => 1,
            'authcode' => '134567890123456789',
            'terminfo' => ['devicetype' => '11', 'termno' => 'T001'],
        ]);

        expect($result->get('trxstatus'))->toBe('SUCCESS');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('11');
        expect($fields['terminfo'] ?? null)->toBe('{"devicetype":"11","termno":"T001"}');
    });

    it('Native', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'order-1003',
            'trxstatus' => 'INIT',
            'payinfo' => 'https://qr.allinpay.com/xxx',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->native([
            'reqsn' => 'order-1003',
            'trxamt' => 100,
            'expiretime' => '20991231235959',
        ]);

        expect($result->get('payinfo'))->toBe('https://qr.allinpay.com/xxx');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('12');
        expect($fields['expiretime'] ?? null)->toBe('20991231235959');
    });

    it('查询', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'order-1001',
            'trxid' => '240101120000000001',
            'trxstatus' => 'SUCCESS',
            'trxamt' => '1',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->query(['reqsn' => 'order-1001']);

        expect($result->get('trxstatus'))->toBe('SUCCESS');
        expect($result->get('reqsn'))->toBe('order-1001');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('12');
    });

    it('查询确认', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'order-1001',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->queryConfirm(['trxid' => '240101120000000001']);

        expect($result->get('retcode'))->toBe('SUCCESS');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('12');
    });

    it('退款', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'refund-1',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->refund([
            'reqsn' => 'refund-1',
            'trxamt' => 1,
            'oldreqsn' => 'order-1001',
        ]);

        expect($result->get('trxstatus'))->toBe('SUCCESS');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('11');
        expect($fields['notify_url'] ?? null)->toBe('https://pay.yansongda.cn/allinpay/notify');
    });

    it('退款缺少原订单', function () {

        Pay::allinpay()->refund([
            'reqsn' => 'refund-1',
            'trxamt' => 1,
        ]);
    })->throws(InvalidParamsException::class);

    it('撤销', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'reqsn' => 'cancel-1',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->cancel([
            'reqsn' => 'cancel-1',
            'trxamt' => 1,
            'oldtrxid' => '240101120000000001',
        ]);

        expect($result->get('retcode'))->toBe('SUCCESS');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('11');
    });

    it('关单', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'trxstatus' => 'CLOSED',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->close([
            'oldreqsn' => 'order-1001',
        ]);

        expect($result->get('trxstatus'))->toBe('CLOSED');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('12');
        expect((string) $this->capturedRequest?->getUri())->toContain('/tranx/close');
    });

    it('Native 关单', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'SUCCESS',
            'retmsg' => '交易成功',
            'cusid' => '9900000',
            'appid' => '000000',
            'trxstatus' => 'CLOSED',
            'randomstr' => 'abc',
        ]));

        $result = Pay::allinpay()->nativeClose([
            'oldreqsn' => 'order-1003',
        ]);

        expect($result->get('trxstatus'))->toBe('CLOSED');

        $fields = $this->capturedBody();

        expect($fields['version'] ?? null)->toBe('12');
        expect((string) $this->capturedRequest?->getUri())->toContain('/unitorder/closenative');
    });
});

describe('response', function () {
    it('业务错误', function () {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'FAIL',
            'retmsg' => '商户不存在',
            'cusid' => '9900000',
            'appid' => '000000',
            'randomstr' => 'abc',
        ]));

        Pay::allinpay()->query(['reqsn' => 'order-x']);
    })->throws(InvalidResponseException::class);

    it('正常', function () {
        $payload = $this->signedResponse([
            'cusid' => '9900000',
            'appid' => '000000',
            'trxid' => '240101120000000001',
            'reqsn' => 'order-1001',
            'trxamt' => '1',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]);

        $result = Pay::allinpay()->callback($payload);

        expect($result->get('trxstatus'))->toBe('SUCCESS');
    });

    it('签名非法', function () {

        Pay::allinpay()->callback([
            'cusid' => '9900000',
            'appid' => '000000',
            'trxstatus' => 'SUCCESS',
            'sign' => base64_encode('bad-sign'),
        ]);
    })->throws(InvalidSignException::class);

    it('成功', function () {
        $response = Pay::allinpay()->success();

        expect($response->getStatusCode())->toBe(200);
        expect((string) $response->getBody())->toBe('success');
    });

    it('从 ServerRequest', function () {
        $payload = $this->signedResponse([
            'cusid' => '9900000',
            'appid' => '000000',
            'trxid' => '240101120000000001',
            'reqsn' => 'order-1001',
            'trxstatus' => 'SUCCESS',
            'randomstr' => 'abc',
        ]);

        $request = (new ServerRequest('POST', '/notify'))
            ->withParsedBody($payload);

        $result = Pay::allinpay()->callback($request);

        expect($result->get('reqsn'))->toBe('order-1001');
    });
});