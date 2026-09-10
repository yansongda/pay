<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AllinpayTrait;

class AllinpayTraitHelper
{
    use AllinpayTrait;
}

class AllinpayTest extends TestCase
{
    public function testShortcutNotFound(): void
    {
        self::expectException(InvalidParamsException::class);

        Pay::allinpay()->foo();
    }

    public function testPay(): void
    {
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

        self::assertSame('SUCCESS', $result->get('retcode'));
        self::assertSame('weixin://wxpay/bizpayurl?pr=xxxx', $result->get('payinfo'));
    }

    public function testPayMissingParams(): void
    {
        $this->expectException(InvalidParamsException::class);

        Pay::allinpay()->unified([
            'trxamt' => 1,
        ]);
    }

    public function testScan(): void
    {
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
            'authcode' => '134567890123456789',
            'terminfo' => ['devicetype' => '11', 'termno' => 'T001'],
        ]);

        self::assertSame('SUCCESS', $result->get('trxstatus'));
    }

    public function testNative(): void
    {
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
            'expiretime' => '10',
        ]);

        self::assertSame('https://qr.allinpay.com/xxx', $result->get('payinfo'));
    }

    public function testQuery(): void
    {
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

        self::assertSame('SUCCESS', $result->get('trxstatus'));
        self::assertSame('order-1001', $result->get('reqsn'));
    }

    public function testQueryConfirm(): void
    {
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

        self::assertSame('SUCCESS', $result->get('retcode'));
    }

    public function testRefund(): void
    {
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

        self::assertSame('SUCCESS', $result->get('trxstatus'));
    }

    public function testRefundMissingOldOrder(): void
    {
        $this->expectException(InvalidParamsException::class);

        Pay::allinpay()->refund([
            'reqsn' => 'refund-1',
            'trxamt' => 1,
        ]);
    }

    public function testCancel(): void
    {
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

        self::assertSame('SUCCESS', $result->get('retcode'));
    }

    public function testClose(): void
    {
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

        self::assertSame('CLOSED', $result->get('trxstatus'));
    }

    public function testNativeClose(): void
    {
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

        self::assertSame('CLOSED', $result->get('trxstatus'));
    }

    public function testBusinessError(): void
    {
        $this->mockHttp($this->signedResponse([
            'retcode' => 'FAIL',
            'retmsg' => '商户不存在',
            'cusid' => '9900000',
            'appid' => '000000',
            'randomstr' => 'abc',
        ]));

        $this->expectException(InvalidResponseException::class);

        Pay::allinpay()->query(['reqsn' => 'order-x']);
    }

    public function testCallback(): void
    {
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

        self::assertSame('SUCCESS', $result->get('trxstatus'));
    }

    public function testCallbackInvalidSign(): void
    {
        $this->expectException(InvalidSignException::class);

        Pay::allinpay()->callback([
            'cusid' => '9900000',
            'appid' => '000000',
            'trxstatus' => 'SUCCESS',
            'sign' => base64_encode('bad-sign'),
        ]);
    }

    public function testSuccess(): void
    {
        $response = Pay::allinpay()->success();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('success', (string) $response->getBody());
    }

    public function testCallbackFromServerRequest(): void
    {
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

        self::assertSame('order-1001', $result->get('reqsn'));
    }

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
        openssl_sign($content, $sign, file_get_contents(__DIR__.'/../Cert/allinpayPlatformPrivateKey.pem'), OPENSSL_ALGO_SHA1);

        $data['sign'] = base64_encode($sign);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mockHttp(array $data): void
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($data)));
        Pay::set(HttpClientInterface::class, $http);
    }
}
