<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Unipay\PreparePlugin;
use Yansongda\Pay\Plugin\Unipay\RadarSignPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class UnipayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_NOT_FOUND);

        Pay::unipay()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_NOT_FOUND);

        Pay::unipay()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [PreparePlugin::class],
            $plugins,
            [RadarSignPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        ), Pay::unipay()->mergeCommonPlugins($plugins));
    }

    public function testFind()
    {
        $response = [
            "accNo" => "dZRvehtDtCcBPbPBVJk+ezkyeXkF07UnbKwr8/012R5XSsDLE4RqpHSLis7WCItKIf6SYUcXCLHYpV9deIfnJfziDGpUdQXnlPfejrw5pKglzjKI/pRYg0ApNomryVBIoUIdTwlpELh048ITEojRgJwzaazWdLaB5iZPeN22E2KP7JkfwsYHEclVpLY4tYEc35IjMpWCs0i8U5KY4qAJ3zXpCFIur4x+9z4gyK89Mwf+csowScvfDbFBsqTpClQlNRJ6lLWOrISNnlCdc1ONK4QVhabDTTWsemM7PSuya2e3imeZFfRYDmSv6W1eMIs0gbRo8BxOr3suUhb2ukPL7w==",
            "accessType" => "0",
            "bizType" => "000000",
            "currencyCode" => "156",
            "encoding" => "utf-8",
            "issuerIdentifyMode" => "0",
            "merId" => "777290058167151",
            "orderId" => "pay20220911041647",
            "origRespCode" => "00",
            "origRespMsg" => "成功[0000000]",
            "queryId" => "512209110416475616528",
            "respCode" => "00",
            "respMsg" => "成功[0000000]",
            "settleAmt" => "1",
            "settleCurrencyCode" => "156",
            "settleDate" => "0911",
            "signMethod" => "01",
            "traceNo" => "561652",
            "traceTime" => "0911041647",
            "txnAmt" => "1",
            "txnSubType" => "01",
            "txnTime" => "20220911041647",
            "txnType" => "01",
            "version" => "5.1.0",
            "signPubKeyCert" => "-----BEGIN CERTIFICATE-----\r
MIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r
MjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r
MTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r
RTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r
veaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r
AQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r
eYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r
t5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r
RCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r
g0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r
sPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r
cJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r
KwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r
A1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r
NTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r
9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r
BQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r
NsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r
4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r
vzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r
HYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r
Q0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r
-----END CERTIFICATE-----",
            "signature" => "b59zh7aztBFNDNmCBOR4189VeG3Vypvuf7H3izqA4etA0AtsjtzV7RXigEFlNXaIHSTSkbAUfPXevY3nX18umwQSFsSphF5C+DiMzBmEJNo9vNiX1kpaeQPlXQfXTDQqmDjdQ4lyeNw/aDx6MHww2D8wpkUem+50cyWOeO1/brHDxwt8N/R2LMRC8QTFIg48N+9UZu6QDIUybQYBAoVS6F0emMxFTpAhVi85RVqIDofStiGhHZw7UocW0hw96+JSExyhc6WXEvmLW41DI3PqRxVu+HacjZqeU+siH7V9pdTPV7Yi3ATznzRhmufvN7r1LJmf/4GkGI1ayM3WLubZjg==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::unipay()->find([
            'txnTime' => '20220911041647',
            'orderId' => 'pay20220911041647',
        ]);

        self::assertArrayHasKey('origRespMsg', $result->all());

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::UNIPAY_FIND_STRING_NOT_SUPPORTED);
        Pay::unipay()->find('123');
    }

    public function testCancel()
    {
        $response = [
            "accessType" => "0",
            "bizType" => "000000",
            "encoding" => "utf-8",
            "merId" => "777290058167151",
            "orderId" => "cancel20220914130301",
            "origQryId" => "652209141301523979028",
            "queryId" => "652209141303013346508",
            "respCode" => "00",
            "respMsg" => "成功[0000000]",
            "signMethod" => "01",
            "txnAmt" => "1",
            "txnSubType" => "00",
            "txnTime" => "20220914130301",
            "txnType" => "31",
            "version" => "5.1.0",
            "signPubKeyCert" => "-----BEGIN CERTIFICATE-----\r
MIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r
MjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r
MTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r
RTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r
veaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r
AQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r
eYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r
t5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r
RCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r
g0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r
sPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r
cJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r
KwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r
A1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r
NTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r
9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r
BQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r
NsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r
4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r
vzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r
HYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r
Q0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r
-----END CERTIFICATE-----",
            "signature" => "wRei4yocbmovkTW72STiApiuUGbYY9Nnz2ypuUPp+qjg2YwYDhDQqnAqYuUKawtbPdgg7IBiMasn32+v++kZgORsbwtVQXnynenQNxQBeZx3NpmaycZKM5phwnYEs7EF8JDGATREHbpHexOge44BsjUIQ/9DyPcM17wHYPxlwa8RaFi4nO3Pcm7CwGX0PuspOkfqy4IH2WuxhZ0B7KQISQobaXAGsqIrol5WHkb3fsAUNweUfcjWXYzhnqqOrrGojoAE1Q/0vGpGXj1oMHphClYwYwGdOQIx/CJzeK3qyTSsbReNPTFZW0u45ItaPzrMsJ4Q5C+rDX1reLCy6KiHQw==",
            ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::unipay()->cancel([
            'txnTime' => date('YmdHis'),
            'txnAmt' => 1,
            'orderId' => 'cancel'.date('YmdHis'),
            'origQryId' => '652209141301523979028'
        ]);

        self::assertArrayHasKey('respMsg', $result->all());

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::UNIPAY_CANCEL_STRING_NOT_SUPPORTED);
        Pay::unipay()->cancel('123');
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::METHOD_NOT_SUPPORTED);

        Pay::unipay()->close('foo');
    }

    public function testRefund()
    {
        $response = [
            "accessType" => "0",
            "bizType" => "000000",
            "encoding" => "utf-8",
            "merId" => "777290058167151",
            "orderId" => "refund20220914130757",
            "origQryId" => "052209141307102430268",
            "queryId" => "052209141307573008998",
            "respCode" => "00",
            "respMsg" => "成功[0000000]",
            "signMethod" => "01",
            "txnAmt" => "1",
            "txnSubType" => "00",
            "txnTime" => "20220914130757",
            "txnType" => "04",
            "version" => "5.1.0",
            "signPubKeyCert" => "-----BEGIN CERTIFICATE-----\r
MIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r
MjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r
MTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r
RTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r
veaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r
AQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r
eYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r
t5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r
RCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r
g0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r
sPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r
cJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r
KwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r
A1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r
NTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r
9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r
BQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r
NsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r
4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r
vzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r
HYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r
Q0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r
-----END CERTIFICATE-----",
            "signature" => "JLP38lt6Na0vInK735sHczfsuny3GhUqLPjTMpweNMINCDfZvbo/qiusbNiqfZ4WU8if5Z+xdgKX5i90rhw5rmHmRIf7U6PJmJuy6hOMbwF04y8IrOGQQVrLRY51/ih19/uMOOMdCXgxRJRbMu/WiTLHSpxwIW54RI0R4D8SWhKBw1B3aeeBahh3tblvDBW+geNgIX9GQOV8+u1nJS/muaPai246bwKa0NcAA0KESlpqCuY2H7KIAzK3e47Q0au/ZHLClzb9MJBnZqPK6ieVMCgXIXjSyVv/31ClPghqsflNYRIjdXhuxsbzQ0gXC9gL6G3cDcle9BvEp/101owqzw==",
        ];

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($response)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::unipay()->refund([
            'txnTime' => date('YmdHis'),
            'txnAmt' => 1,
            'orderId' => 'refund'.date('YmdHis'),
            'origQryId' => '052209141307102430268'
        ]);

        self::assertArrayHasKey('respMsg', $result->all());
    }

    public function testCallback()
    {
        $input = [
            "accNo" => "ORRSXWY1kMr8UJNxGx9xKPuO0Uhm8JT8aQV3sWswJfIsj/grkjauH4soyAtiqB9XwQotZOwmUAs/pkMupUkfiX9npdFGGEUEc5gqq+lcEwyD7tLmd2WBzRvcEvvjAKMKwTCFDxmQbIrP48ocIVhPoZ87ZQtQM5MIyJYedrzPRlt6BzRddUPGU1gJwDA8APDx3iyNl8EAfenJw7DUDZimmhbE1VSRmQm/iqgJurI7juq/6ztDHZHv4ys1eN9JYkwhcKxCjsWpwXTSy0PGvDXhsAZsDuNXHsjI8JLhHXvTDaU2+gc289LZPiwpr4Ah/reIuPWrIHubchYm2XTqQlUAaw==",
            "accessType" => "0",
            "bizType" => "000201",
            "currencyCode" => "156",
            "encoding" => "utf-8",
            "exchangeRate" => "0",
            "merId" => "777290058167151",
            "orderId" => "yansongda20220908132206",
            "queryId" => "782209081322060674028",
            "respCode" => "00",
            "respMsg" => "success",
            "settleAmt" => "1",
            "settleCurrencyCode" => "156",
            "settleDate" => "0908",
            "signMethod" => "01",
            "signPubKeyCert" => "-----BEGIN CERTIFICATE-----\r
MIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r
MjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r
MTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r
RTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r
veaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r
AQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r
eYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r
t5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r
RCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r
g0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r
sPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r
cJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r
KwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r
A1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r
NTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r
9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r
BQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r
NsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r
4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r
vzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r
HYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r
Q0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r
-----END CERTIFICATE-----",
            "signature" => "JeA4S2+6TbGo9yjXDUvV5A2E3oJbunoCcZ66exN6xR3OH/5PNDK1VSV1Mq7XhVdxzkTeREUveiOYHalqoagRkh71nsHVvruwGbk6azygXSaawuO5tF67UIqNd4Mbufwh1KhbVpEkKbOETUvRhFcdon0fulE97I83eMSk52INHt8E1xk8NdbhyUadSlp+Uv30AKx70PpQbTGmVS3PJfd+Whj0b7LnvZKeC+BS1kUOtIKlcZO+gBoTigvCIJqj51kBrcBCs+x+VaeGm7EYBBhGSERpfQhQ4n+eJBwLdBeZ0/dNbo3iELjvVMx0n9KoW4klvUJhaH5LALA8pV02SbZv4Q==",
            "traceNo" => "067402",
            "traceTime" => "0908132206",
            "txnAmt" => "1",
            "txnSubType" => "01",
            "txnTime" => "20220908132206",
            "txnType" => "01",
            "version" => "5.1.0",
        ];

        $result = Pay::unipay()->callback($input);
        self::assertNotEmpty($result->all());

        $result = Pay::unipay()->callback((new ServerRequest('POST', 'https://pay.yansongda.cn'))->withParsedBody($input));
        self::assertNotEmpty($result->all());
    }
}
