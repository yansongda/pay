<?php

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class UnipayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::unipay()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::unipay()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config();
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignaturePlugin::class, AddPayloadBodyPlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ParserPlugin::class],
        ), Pay::unipay()->mergeCommonPlugins($plugins));
    }

    public function testWebPay()
    {
        $response = Pay::unipay()->web([
            '_config' => 'sandbox',
            'txnTime' => '20240105145046',
            'txnAmt' => 1,
            'orderId' => 'pay20240105145046',
            '_return_rocket' => true,
        ]);

        self::assertEquals('txnTime=20240105145046&txnAmt=1&orderId=pay20240105145046&certId=69903319369&encoding=utf-8&signature=hBJV0%2F%2BJEeBe8Us%2FOBOQE6cS04HBKxV4L5PKN6nb6V1T%2FqIKmd98uXzyRIaXgvOWZcDUDNGKPjs7cYwifjnhUUX6284YSWuy2MgCZ75ddd9NVtYftSgEwPawzU%2FkzagoQqn0ahr%2FjUOGYHNDPnnyszfwZgUGRol8yhlePDz%2Btjfdur9PP3487%2FuKRa5s1PCrLb0PTUMEi6oiO52oz5ms4vKuP2v3YBBo5yyW6oqSQ%2FSQxwh971fpfDV3ZGBGL8KOMpPqyhgLJwTkH1KA63kcQPdiVOoNdV%2Bb4jw%2BPkEU9059ia0cNHB3wyOfX8st0oiVSR9PQ30WXwnL%2BOg3xQ6IoQ%3D%3D&bizType=000201&accessType=0&merId=777290058167151&currencyCode=156&channelType=07&signMethod=01&txnType=01&txnSubType=01&frontUrl=http%3A%2F%2F127.0.0.1%3A8000%2Funipay%2Freturn&backUrl=https%3A%2F%2Fyansongda.cn%2Funipay%2Fnotify&version=5.1.0', (string) $response->getRadar()->getBody());
        self::assertEquals('application/x-www-form-urlencoded;charset=utf-8', $response->getRadar()->getHeaderLine('Content-Type'));
    }

    public function testPosQra()
    {
        $response = '<xml><charset><![CDATA[UTF-8]]></charset> <code><![CDATA[9999999]]></code> <err_code><![CDATA[NOAUTH]]></err_code> <err_msg><![CDATA[此商家涉嫌违规，收款功能已被限制，暂无法支付。商家可以登录微信商户平台/微信支付商家助手小程序查看原因和解决方案。]]></err_msg> <mch_id><![CDATA[QRA29045311KKR1]]></mch_id> <need_query><![CDATA[N]]></need_query> <nonce_str><![CDATA[UhxOr4kzerPGku9wCaVQyfd1zisoAnAm]]></nonce_str> <result_code><![CDATA[1]]></result_code> <sign><![CDATA[4B9B2AA73A05CBC32CFDCB4456E12EBA]]></sign> <sign_type><![CDATA[MD5]]></sign_type> <status><![CDATA[0]]></status> <transaction_id><![CDATA[95516000379952690603566602920171]]></transaction_id> <version><![CDATA[2.0]]></version> </xml>';

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
        Pay::set(HttpClientInterface::class, $http);

        $response = Pay::unipay()->pos([
            '_config' => 'qra',
            '_action' => 'qra',
            'out_trade_no' => 'pos-qra-20240106163401',
            'body' => '测试商品',
            'total_fee' => 1,
            'mch_create_ip' => '127.0.0.1',
            'auth_code' => '131969896307360385',
            'op_device_id' => '123',
            'terminal_info' => json_encode([
                'device_type' => '07',
                'terminal_id' => '123',
            ]),
            '_return_rocket' => true,
        ]);

        // 有随机串，只验证部分
        self::assertStringContainsString('<xml><out_trade_no><![CDATA[pos-qra-20240106163401]]></out_trade_no><body><![CDATA[测试商品]]></body><total_fee>1</total_fee><mch_create_ip><![CDATA[127.0.0.1]]></mch_create_ip><auth_code>131969896307360385</auth_code><op_device_id>123</op_device_id><terminal_info><![CDATA[{"device_type":"07","terminal_id":"123"}]]></terminal_info><service><![CDATA[unified.trade.micropay]]></service><charset><![CDATA[UTF-8]]></charset><sign_type><![CDATA[MD5]]></sign_type><mch_id><![CDATA[QRA29045311KKR1]]></mch_id>', (string) $response->getRadar()->getBody());
        self::assertEquals('https://qra.95516.com/pay/gateway', (string) $response->getRadar()->getUri());
        self::assertEquals('application/x-www-form-urlencoded;charset=utf-8', $response->getRadar()->getHeaderLine('Content-Type'));
    }

    public function testQuery()
    {
        $response = "accNo=cDg4jpD9tKxlI4si91lomXcSjQeOyOxEFLmMc9BLmsJrLPMqmN0JIUkJUD1MVYCfpXRsGKFGDwerpR0LylHiKXnB1ZX0zS4OleY2kSvgX6J2X24TPHkY+EKS7F2kfuYW8yHF+OgUEoUqPa/WO/qzigGci3sSGLiD/uCIljuDOfskQdgXYpsQANPmmgqCOC+TqtDSjYfPKZ3o6SbJJSn4lOvUy3FF/re7PFCShVghJVO1XRJFw0d0VNUkhWs2VzdUl1ZwkDVXcFWB39REEOWDHNEBoBgvkfVSb9j6KV7K3gUNXkfqHqk7zBTtg67jAi/kudAN4U1k9Y5UMGj5mVbu2g==&accessType=0&bizType=000000&currencyCode=156&encoding=utf-8&issuerIdentifyMode=0&merId=777290058167151&orderId=pay20240105145046&origRespCode=00&origRespMsg=成功[0000000]&queryId=442401051450467846498&respCode=00&respMsg=成功[0000000]&settleAmt=1&settleCurrencyCode=156&settleDate=0105&signMethod=01&traceNo=784649&traceTime=0105145046&txnAmt=1&txnSubType=01&txnTime=20240105145046&txnType=01&version=5.1.0&signPubKeyCert=-----BEGIN CERTIFICATE-----\r\nMIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r\nQ04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r\ncml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r\nMjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r\nMTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r\nRTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r\nveaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r\nAQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r\neYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r\nt5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r\nRCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r\ng0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r\nsPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r\ncJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r\nKwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r\nA1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r\nNTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r\n9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r\nBQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r\nNsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r\n4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r\nvzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r\nHYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r\nQ0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r\n-----END CERTIFICATE-----&signature=sEJYCC94bvr5KJ12EVeid6kVoyviipkieSp5I9r7baINMZdTxJZlFLIpqI/58s/QMgQXO7I7W0d//YUYXgR2SxZy0n56df+oTUMiXEchwngF/ksiegh0HndsY9ZdYcfwqzD1repppuKGRhOmFL4oUZFPOdLZ8t6QJEcdpEKh21eZ1+fw+Fd2gAfBaIJTx1fg0ZNarvOh1AeeJu48c399p3DZKYxRX04sg5grr6By0GCE3h8UdmSWRRoEdiT/jMKB+xnSi+JL8x04NXfqbjSJcmMebw5uIZ3Bst93Aa2jR3GgK+52j78Al8AIy4nmI0WncAmxAToMrN6AJZaUpj1S5Q==";

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::unipay()->query([
            '_config' => 'sandbox',
            'txnTime' => '20240105145046',
            'orderId' => 'pay20240105145046',
            '_return_rocket' => true,
        ]);

        self::assertEquals('txnTime=20240105145046&orderId=pay20240105145046&certId=69903319369&encoding=utf-8&signature=lH8zZVGlsodhJ9U90v61gacQbS0NRPPDa%2Bpt5WUUIjLzQhOob0ZKRUR%2Bk67wK5w0n9wDUkg%2FiS0w5%2FOzAaj7jsRQzbBemSddciS7Pz6Npze%2F7bUdYA%2FHsWbw898gII16FJgohCPSOhs%2BTDk77kwriKuxrtUuDmnGFKW36Jjmglxp4e97zypV%2F08lFxvJBVIxFq8vwKG%2FzQVYTnqLGcCh8SKWae35SDXU2GMEjQ6QQGseN3kbs1Fs2uOdnzkfXJxQrEISVHr9oPoWJC%2BBjr%2FGZrbSVy3Vp9ymKzg8y%2B7bfSg749iCLJCoEhz9KI5YYsmOXG7RZYkx99tYk1XhCTyRsw%3D%3D&bizType=000000&accessType=0&merId=777290058167151&signMethod=01&txnType=00&txnSubType=00&version=5.1.0', (string) $result->getRadar()->getBody());
        self::assertArrayHasKey('origRespMsg', $result->getDestination()->all());
    }

    public function testCancel()
    {
        $response = "accessType=0&bizType=000000&encoding=utf-8&merId=777290058167151&orderId=cancelpay20240105164725&origQryId=652401051647252574008&queryId=652401051647252421528&respCode=00&respMsg=成功[0000000]&signMethod=01&txnAmt=1&txnSubType=00&txnTime=20240105164725&txnType=31&version=5.1.0&signPubKeyCert=-----BEGIN CERTIFICATE-----\r\nMIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r\nQ04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r\ncml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r\nMjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r\nMTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r\nRTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r\nveaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r\nAQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r\neYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r\nt5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r\nRCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r\ng0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r\nsPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r\ncJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r\nKwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r\nA1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r\nNTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r\n9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r\nBQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r\nNsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r\n4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r\nvzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r\nHYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r\nQ0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r\n-----END CERTIFICATE-----&signature=C3QoNG8pgA8Y8JRWPJBfnJCTI00ujWwNdA5dJC8Sq4oddIzc8E34jSJW9YLHTMkgB0AV6eZ7z7eGKCvq7UkOQf6mXM7agiCxPEAduYVAQ74z+HZbdMhYwqkEMb4WulRdossDz+qtSi28C/+WM0jlXeJe695dfl7SjnVl0d3Pk5Mg1Xxpc64VRtR8l4iJuNxzVaDRHvINMOrCa6NYg+W4hpyRJc3BM3Abg2O/9Zu+raTk9yjCEWl0t9C8K7Kym03wkXFhNBqUYG2OBIjqHMkTnUVQyRkARGv8uDcPAg7EYLk8T2U6NXqLIwEAvTc7yEik6YP3/27SqkwMquH4PXqo4w==";

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::unipay()->cancel([
            '_config' => 'sandbox',
            'txnTime' => '20240105164725',
            'txnAmt' => 1,
            'orderId' => 'cancelpay20240105164725',
            'origQryId' => '652401051647252574008',
            '_return_rocket' => true,
        ]);

        self::assertEquals('txnTime=20240105164725&txnAmt=1&orderId=cancelpay20240105164725&origQryId=652401051647252574008&certId=69903319369&encoding=utf-8&signature=tEttBeAG77v1R5L80gFjKK2jZjgTutY2mUA7f4MPBze9fYoOMzixbboqtGSuG%2BGFHGSkbHP95ECYxgzs4EP5zEG6bKSlPxWyoUao3crqX%2BFUwav4qJpRv6EQ7UD0Uh1eNcNMaazPad4x8uijJVNqUDaUz68RFKB59YZwLd6jYjMz7hLM5ip3bkQ7%2Bnw%2BwLCDvpT43HRgiNFETSHf%2Buku4zeuhnY%2F6zDwiieft6Na7Hy9z4OZlYB8Rg%2FVFTxj28uH0QIRYh%2F38z%2FUFrFi7OfyEBzgHqP7MY61JrHOVEIT2NSLRXg3FuYAfoCI4bqBvcoUquzr5JkA3drLu7s%2FlzyQHA%3D%3D&bizType=000000&accessType=0&merId=777290058167151&channelType=07&signMethod=01&txnType=31&txnSubType=00&backUrl=https%3A%2F%2Fyansongda.cn%2Funipay%2Fnotify&version=5.1.0', (string) $result->getRadar()->getBody());
        self::assertArrayHasKey('respMsg', $result->getDestination()->all());
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::unipay()->close(['foo']);
    }

    public function testRefund()
    {
        $response = "accessType=0&bizType=000000&encoding=utf-8&merId=777290058167151&orderId=refundpay20240105165842&origQryId=052401051658427862748&queryId=052401051658427863998&respCode=00&respMsg=成功[0000000]&signMethod=01&txnAmt=1&txnSubType=00&txnTime=20240105165842&txnType=04&version=5.1.0&signPubKeyCert=-----BEGIN CERTIFICATE-----\r\nMIIEYzCCA0ugAwIBAgIFEDkwhTQwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC\r\nQ04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv\r\ncml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMjAwNzMxMDExOTE2WhcN\r\nMjUwNzMxMDExOTE2WjCBljELMAkGA1UEBhMCY24xEjAQBgNVBAoTCUNGQ0EgT0NB\r\nMTEWMBQGA1UECxMNTG9jYWwgUkEgT0NBMTEUMBIGA1UECxMLRW50ZXJwcmlzZXMx\r\nRTBDBgNVBAMMPDA0MUA4MzEwMDAwMDAwMDgzMDQwQOS4reWbvemTtuiBlOiCoeS7\r\nveaciemZkOWFrOWPuEAwMDAxNjQ5NTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCC\r\nAQoCggEBAMHNa81t44KBfUWUgZhb1YTx3nO9DeagzBO5ZEE9UZkdK5+2IpuYi48w\r\neYisCaLpLuhrwTced19w2UR5hVrc29aa2TxMvQH9s74bsAy7mqUJX+mPd6KThmCr\r\nt5LriSQ7rDlD0MALq3yimLvkEdwYJnvyzA6CpHntP728HIGTXZH6zOL0OAvTnP8u\r\nRCHZ8sXJPFUkZcbG3oVpdXQTJVlISZUUUhsfSsNdvRDrcKYY+bDWTMEcG8ZuMZzL\r\ng0N+/spSwB8eWz+4P87nGFVlBMviBmJJX8u05oOXPyIcZu+CWybFQVcS2sMWDVZy\r\nsPeT3tPuBDbFWmKQYuu+gT83PM3G6zMCAwEAAaOB9DCB8TAfBgNVHSMEGDAWgBTP\r\ncJ1h6518Lrj3ywJA9wmd/jN0gDBIBgNVHSAEQTA/MD0GCGCBHIbvKgEBMDEwLwYI\r\nKwYBBQUHAgEWI2h0dHA6Ly93d3cuY2ZjYS5jb20uY24vdXMvdXMtMTQuaHRtMDkG\r\nA1UdHwQyMDAwLqAsoCqGKGh0dHA6Ly91Y3JsLmNmY2EuY29tLmNuL1JTQS9jcmw3\r\nNTAwMy5jcmwwCwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTmzk7XEM/J/sd+wPrMils3\r\n9rJ2/DAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwDQYJKoZIhvcNAQEF\r\nBQADggEBAJLbXxbJaFngROADdNmNUyVxPtbAvK32Ia0EjgDh/vjn1hpRNgvL4flH\r\nNsGNttCy8afLJcH8UnFJyGLas8v/P3UKXTJtgrOj1mtothv7CQa4LUYhzrVw3UhL\r\n4L1CTtmE6D1Kf3+c2Fj6TneK+MoK9AuckySjK5at6a2GQi18Y27gVF88Nk8bp1lJ\r\nvzOwPKd8R7iGFotuF4/8GGhBKR4k46EYnKCodyIhNpPdQfpaN5AKeS7xeLSbFvPJ\r\nHYrtBsI48jUK/WKtWBJWhFH+Gty+GWX0e5n2QHXHW6qH62M0lDo7OYeyBvG1mh9u\r\nQ0C300Eo+XOoO4M1WvsRBAF13g9RPSw=\r\n-----END CERTIFICATE-----&signature=c++EAuubwRkvr2MVyM9zyjbdH3RMRK/L1ttftpJ4fkl4ZSY1BjyRbTj5fx/2+Z/eH4dqPNfFEQt8egVVWhF/k7PaD8tLTaueeUIPwyjnEIWmqNtVbJtzKexCouGc8wtYDHZYxTJTgo6BW7GEgO5xD6Qpxq801Bb9Zto8uhn4BUP4HI7UsxHHIzP9JYhL2cqz2B8gb3AJHpLMEBpYv+Kb3mwq8ZFgpGaieCAFFGGWImUx1+MgCzLFoe3SKlTF13nbr39Cd3AHuDJnbN+uG1N6AwUtLu12Zzq/6SM+/dqiE0v5SpvB/PeRj9KQeiGDRg/ho9larqB+D3y0FjU13EeHng==";

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::unipay()->refund([
            '_config' => 'sandbox',
            'txnTime' => '20240105165842',
            'txnAmt' => 1,
            'orderId' => 'refundpay20240105165842',
            'origQryId' => '052401051658427862748',
            '_return_rocket' => true
        ]);

        self::assertEquals('txnTime=20240105165842&txnAmt=1&orderId=refundpay20240105165842&origQryId=052401051658427862748&certId=69903319369&encoding=utf-8&signature=oeJQFbkSbXWNs1HfgNKqq4%2BPBA3xtKZDNT62VHAcn7F5OAjzkSHMC0bArsoY2jtw2wRVTg7drFIlYJwgN04OwikM2KRLcLdHzqTCZOIoSjo2CzfpSS6nAExHo5%2BPzKUIkO1KHbzV3xkID6F%2Bdt9CWzFbRy81gqtZZLK%2BRuYFmu%2FT9f5EcjNBbiMRS7WNXckrSZc2Ny%2FCRhI9qujqK%2B2ANKFkcQ%2BGrZ24PHE1J73eUt8dNTSTt0snkckh5sK3oBTH2oHO%2BhizzfLGHYjLcUVCisN4JwsAjD6%2BMyxImYK1QkYhhCTJ3Owop5HuwAg2zSeq7TAfkimhtVVCdfNA1GNHuA%3D%3D&bizType=000000&accessType=0&merId=777290058167151&channelType=07&signMethod=01&txnType=04&txnSubType=00&backUrl=https%3A%2F%2Fyansongda.cn%2Funipay%2Fnotify&version=5.1.0', (string) $result->getRadar()->getBody());
        self::assertArrayHasKey('respMsg', $result->getDestination()->all());
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

    public function testSuccess()
    {
        $result = Pay::unipay()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertEquals('success', (string) $result->getBody());
    }
}
