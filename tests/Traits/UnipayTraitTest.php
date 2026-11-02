<?php

declare(strict_types=1);

use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Stubs\UnipayTraitStub;

it('银联验签', function () {
    $contents = "accNo=ORRSXWY1kMr8UJNxGx9xKPuO0Uhm8JT8aQV3sWswJfIsj/grkjauH4soyAtiqB9XwQotZOwmUAs/pkMupUkfiX9npdFGGEUEc5gqq+lcEwyD7tLmd2WBzRvcEvvjAKMKwTCFDxmQbIrP48ocIVhPoZ87ZQtQM5MIyJYedrzPRlt6BzRddUPGU1gJwDA8APDx3iyNl8EAfenJw7DUDZimmhbE1VSRmQm/iqgJurI7juq/6ztDHZHv4ys1eN9JYkwhcKxCjsWpwXTSy0PGvDXhsAZsDuNXHsjI8JLhHXvTDaU2+gc289LZPiwpr4Ah/reIuPWrIHubchYm2XTqQlUAaw==&accessType=0&bizType=000201&currencyCode=156&encoding=utf-8&exchangeRate=0&merId=777290058167151&orderId=yansongda20220908132206&queryId=782209081322060674028&respCode=00&respMsg=success&settleAmt=1&settleCurrencyCode=156&settleDate=0908&signMethod=01&signPubKeyCert=-----BEGIN CERTIFICATE-----\r
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
-----END CERTIFICATE-----&traceNo=067402&traceTime=0908132206&txnAmt=1&txnSubType=01&txnTime=20220908132206&txnType=01&version=5.1.0";
    $sign = 'JeA4S2+6TbGo9yjXDUvV5A2E3oJbunoCcZ66exN6xR3OH/5PNDK1VSV1Mq7XhVdxzkTeREUveiOYHalqoagRkh71nsHVvruwGbk6azygXSaawuO5tF67UIqNd4Mbufwh1KhbVpEkKbOETUvRhFcdon0fulE97I83eMSk52INHt8E1xk8NdbhyUadSlp+Uv30AKx70PpQbTGmVS3PJfd+Whj0b7LnvZKeC+BS1kUOtIKlcZO+gBoTigvCIJqj51kBrcBCs+x+VaeGm7EYBBhGSERpfQhQ4n+eJBwLdBeZ0/dNbo3iELjvVMx0n9KoW4klvUJhaH5LALA8pV02SbZv4Q==';

    UnipayTraitStub::verifyUnipaySign(UnipayTraitStub::getProviderConfig('unipay'), $contents, $sign);

    expect(true)->toBeTrue();

    Artful::get(ConfigInterface::class)->set('unipay.default.unipay_public_cert_path', null);
    UnipayTraitStub::verifyUnipaySign(new UnipayConfig(['mch_secret_key' => 'foo']), $contents, $sign);
})->throws(InvalidConfigException::class, '配置异常： 缺少银联配置 -- [unipay_public_cert_path]', Exception::CONFIG_UNIPAY_INVALID);

it('银联验签为空', function () {
    UnipayTraitStub::verifyUnipaySign(new UnipayConfig(['mch_secret_key' => 'foo']), '', '');
})->throws(InvalidSignException::class, '签名异常: 银联签名为空', Exception::SIGN_EMPTY);

it('获取银联 URL', function () {
    $config = new UnipayConfig(['mch_secret_key' => 'foo']);

    expect(UnipayTraitStub::getUnipayUrl($config, new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');
    expect(UnipayTraitStub::getUnipayUrl($config, new Collection(['_url' => '/api/v1/yansongda'])))->toEqual('https://gateway.95516.com/api/v1/yansongda');
    expect(UnipayTraitStub::getUnipayUrl(new UnipayConfig(['mch_secret_key' => 'foo', 'mode' => PayMode::SERVICE]), new Collection(['_service_url' => '/api/v1/service/yansongda'])))->toEqual('https://gateway.95516.com/api/v1/service/yansongda');
    expect(UnipayTraitStub::getUnipayUrl(new UnipayConfig(['mch_secret_key' => 'foo', 'mode' => PayMode::SERVICE]), new Collection(['_url' => '/foo', '_service_url' => '/api/v1/service/yansongda'])))->toEqual('https://gateway.95516.com/api/v1/service/yansongda');

    UnipayTraitStub::getUnipayUrl($config, new Collection([]));
})->throws(InvalidParamsException::class, null, Exception::PARAMS_UNIPAY_URL_MISSING);

it('获取银联请求 body', function () {
    expect(UnipayTraitStub::getUnipayBody(new Collection(['_body' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');

    UnipayTraitStub::getUnipayBody(new Collection([]));
})->throws(InvalidParamsException::class, null, Exception::PARAMS_UNIPAY_BODY_MISSING);

it('获取银联 QRA 签名', function () {
    $config = UnipayTraitStub::getProviderConfig('unipay', ['_config' => 'qra']);

    $payload = [
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
        'service' => 'unified.trade.micropay',
        'charset' => 'UTF-8',
        'sign_type' => 'MD5',
        'mch_id' => 'QRA29045311KKR1',
        'nonce_str' => 'UhxOr4kzerPGku9wCaVQyfd1zisoAnAm',
    ];

    expect(UnipayTraitStub::getUnipaySignQra($config, $payload))->toEqual('DB571C2F75C657B42485CD07470F0FB9');

    UnipayTraitStub::getUnipaySignQra(new UnipayConfig(['mch_cert_path' => 'foo', 'mch_cert_password' => 'bar']), $payload);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_UNIPAY_INVALID);

it('银联 QRA 验签', function () {
    $payload = [
        'charset' => 'UTF-8',
        'code' => '9999999',
        'err_code' => 'NOAUTH',
        'err_msg' => '此商家涉嫌违规，收款功能已被限制，暂无法支付。商家可以登录微信商户平台/微信支付商家助手小程序查看原因和解决方案。',
        'mch_id' => 'QRA29045311KKR1',
        'need_query' => 'N',
        'nonce_str' => 'UhxOr4kzerPGku9wCaVQyfd1zisoAnAm',
        'result_code' => '1',
        'sign' => '4B9B2AA73A05CBC32CFDCB4456E12EBA',
        'sign_type' => 'MD5',
        'status' => '0',
        'transaction_id' => '95516000379952690603566602920171',
        'version' => '2.0',
    ];

    /** @var UnipayConfig $config */
    $config = UnipayTraitStub::getProviderConfig('unipay', ['_config' => 'qra']);

    UnipayTraitStub::verifyUnipaySignQra($config, $payload);
    expect(true)->toBeTrue();

    UnipayTraitStub::verifyUnipaySignQra(new UnipayConfig(['mch_cert_path' => 'foo', 'mch_cert_password' => 'bar']), $payload);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_UNIPAY_INVALID);

it('银联 QRA 验签失败', function () {
    $payload = [
        'charset' => 'UTF-8',
        'code' => '9999999',
        'err_code' => 'NOAUTH',
        'err_msg' => '此商家涉嫌违规，收款功能已被限制，暂无法支付。商家可以登录微信商户平台/微信支付商家助手小程序查看原因和解决方案。',
        'mch_id' => 'QRA29045311KKR1',
        'need_query' => 'N',
        'nonce_str' => 'UhxOr4kzerPGku9wCaVQyfd1zisoAnAm',
        'result_code' => '1',
        'sign' => '4B9B2AA73A05CBC32CFDCB4456E12EB1',
        'sign_type' => 'MD5',
        'status' => '0',
        'transaction_id' => '95516000379952690603566602920171',
        'version' => '2.0',
    ];

    /** @var UnipayConfig $config */
    $config = UnipayTraitStub::getProviderConfig('unipay', ['_config' => 'qra']);

    UnipayTraitStub::verifyUnipaySignQra($config, $payload);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('银联 QRA 验签为空', function () {
    $payload = [
        'charset' => 'UTF-8',
        'code' => '9999999',
        'err_code' => 'NOAUTH',
        'err_msg' => '此商家涉嫌违规，收款功能已被限制，暂无法支付。商家可以登录微信商户平台/微信支付商家助手小程序查看原因和解决方案。',
        'mch_id' => 'QRA29045311KKR1',
        'need_query' => 'N',
        'nonce_str' => 'UhxOr4kzerPGku9wCaVQyfd1zisoAnAm',
        'result_code' => '1',
        'sign_type' => 'MD5',
        'status' => '0',
        'transaction_id' => '95516000379952690603566602920171',
        'version' => '2.0',
    ];

    /** @var UnipayConfig $config */
    $config = UnipayTraitStub::getProviderConfig('unipay', ['_config' => 'qra']);

    UnipayTraitStub::verifyUnipaySignQra($config, $payload);
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

