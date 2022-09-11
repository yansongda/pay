<?php

namespace Yansongda\Pay\Tests\Plugin\Unipay;

use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Plugin\Unipay\LaunchPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class LaunchPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Unipay\LaunchPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new LaunchPlugin();
    }

    public function testShouldNotDoRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestParser::class);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }

    public function testNormal()
    {
        $response = Collection::wrap([
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
        ]);

        $rocket = new Rocket();
        $rocket->setDestination($response);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        unset($response['signature']);

        self::assertEqualsCanonicalizing($response->toArray(), $result->getDestination()->toArray());
    }

    public function testArrayDirection()
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

        $rocket = new Rocket();
        $rocket->setDestination($response);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        unset($response['signature']);

        self::assertEqualsCanonicalizing($response, $result->getDestination()->toArray());
    }
}
