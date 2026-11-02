<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\RequestInterface;
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
use Yansongda\Pay\Traits\AllinpayTrait;

class AllinpayTraitHelper
{
use AllinpayTrait;
}

uses(AllinpayTestHelpers::class);

it('shortcut not found', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->foo();
});

it('pay', function () {
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

it('pay missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->unified([
        'trxamt' => 1,
    ]);
});

it('pay missing trxamt', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->unified([
        'reqsn' => 'order-1001',
        'paytype' => 'W02',
    ]);
});

it('sign without payload', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    Pay::allinpay()->pay([AddPayloadSignPlugin::class], []);
});

it('sign missing secret key', function () {
    Pay::config([
        '_force' => true,
        'allinpay' => [
            'default' => [
                'cusid' => '9900000',
                'appid' => '000000',
                'allinpay_public_key' => __DIR__.'/../Cert/allinpayPlatformPublicKey.pem',
                'mode' => PayMode::SANDBOX,
            ],
        ],
    ]);

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALLINPAY_INVALID);

    Pay::allinpay()->pay([AddPayloadSignPlugin::class], ['reqsn' => 'order-1001']);
});

it('scan missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->scan([
        'reqsn' => 'order-1002',
    ]);
});

it('native missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->native([
        'reqsn' => 'order-1003',
        'trxamt' => 1,
    ]);
});

it('query missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->query([]);
});

it('query confirm missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->queryConfirm([]);
});

it('refund missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->refund([
        'oldreqsn' => 'order-1001',
    ]);
});

it('cancel missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->cancel([
        'oldtrxid' => '240101120000000001',
    ]);
});

it('cancel missing old order', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->cancel([
        'reqsn' => 'cancel-1',
        'trxamt' => 1,
    ]);
});

it('close missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->close([]);
});

it('native close missing params', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->nativeClose([]);
});

it('http status code error', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn(
        new Response(500, ['Content-Type' => 'application/json'], json_encode(['retcode' => 'SUCCESS', 'retmsg' => 'ok']))
    );
    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    Pay::allinpay()->query(['reqsn' => 'order-x']);
});

it('callback with invalid request', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

    Pay::allinpay()->pay([CallbackPlugin::class], ['_request' => 'invalid', '_params' => []]);
});

it('callback from globals', function () {
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

it('array params normalized to json string', function () {
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

it('scan', function () {
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

it('native', function () {
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

it('query', function () {
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

it('query confirm', function () {
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

it('refund', function () {
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

it('refund missing old order', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::allinpay()->refund([
        'reqsn' => 'refund-1',
        'trxamt' => 1,
    ]);
});

it('cancel', function () {
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

it('close', function () {
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

it('native close', function () {
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

it('business error', function () {
    $this->mockHttp($this->signedResponse([
        'retcode' => 'FAIL',
        'retmsg' => '商户不存在',
        'cusid' => '9900000',
        'appid' => '000000',
        'randomstr' => 'abc',
    ]));

    $this->expectException(InvalidResponseException::class);

    Pay::allinpay()->query(['reqsn' => 'order-x']);
});

it('callback', function () {
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

it('callback invalid sign', function () {
    $this->expectException(InvalidSignException::class);

    Pay::allinpay()->callback([
        'cusid' => '9900000',
        'appid' => '000000',
        'trxstatus' => 'SUCCESS',
        'sign' => base64_encode('bad-sign'),
    ]);
});

it('success', function () {
    $response = Pay::allinpay()->success();

    expect($response->getStatusCode())->toBe(200);
    expect((string) $response->getBody())->toBe('success');
});

it('callback from server request', function () {
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

trait AllinpayTestHelpers
{
    private ?RequestInterface $capturedRequest = null;
    
    private function signedResponse(array $data): array
    {
        $data = array_filter($data, static fn ($v) => null !== $v);
        $content = AllinpayTraitHelper::getAllinpaySignContent($data);
    
        $sign = '';
        openssl_sign($content, $sign, file_get_contents(__DIR__.'/../Cert/allinpayPlatformPrivateKey.pem'), OPENSSL_ALGO_SHA1);
    
        $data['sign'] = base64_encode($sign);
    
        return $data;
    }
    
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
    
    private function capturedBody(): array
    {
        $fields = [];
        parse_str((string) ($this->capturedRequest?->getBody() ?? ''), $fields);
    
        return $fields;
    }
}
