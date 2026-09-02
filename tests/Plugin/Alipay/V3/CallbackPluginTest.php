<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Alipay\V3\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testNormalPublicKeyMode(): void
    {
        $form = $this->makeSignedForm();

        $request = (new ServerRequest('POST', 'https://pay.yansongda.cn/alipay/notify'))->withParsedBody($form);

        $rocket = (new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'alipay-v3']]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());
        self::assertInstanceOf(Collection::class, $result->getPayload());
        self::assertSame($form, $result->getPayload()->all());
        self::assertSame($form, $result->getDestination()->all());
        self::assertSame($request, $result->getDestinationOrigin());
    }

    public function testNormalCertMode(): void
    {
        $form = $this->makeSignedForm(['_config' => 'alipay-v3-cert']);

        $request = (new ServerRequest('POST', 'https://pay.yansongda.cn/alipay/notify'))->withParsedBody($form);

        $rocket = (new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'alipay-v3-cert']]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());
        self::assertSame($form, $result->getPayload()->all());
    }

    public function testEscapedParamsPromotedToTop(): void
    {
        // `_params` 提升后 `_config` 到顶层：逃生通道回调可用
        $form = $this->makeSignedForm(['_config' => 'alipay-v3']);

        $request = (new ServerRequest('POST', 'https://pay.yansongda.cn/alipay/notify'))->withParsedBody($form);

        $rocket = (new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'alipay-v3']]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        // 顶层 params 被整体替换为 `_params` 的内容（非合并）
        self::assertSame(['_config' => 'alipay-v3'], $result->getParams());
        self::assertSame($form, $result->getPayload()->all());
    }

    public function testTamperedParams(): void
    {
        $form = $this->makeSignedForm();
        $form['total_amount'] = '999.00';

        $request = (new ServerRequest('POST', 'https://pay.yansongda.cn/alipay/notify'))->withParsedBody($form);

        $rocket = (new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'alipay-v3']]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testMissingSign(): void
    {
        $form = [
            'app_id' => 'alipay_v3_test_app_id',
            'trade_no' => '2023122122001499160501589436',
            'out_trade_no' => '1703147160',
            'trade_status' => 'TRADE_SUCCESS',
            'sign_type' => 'RSA2',
        ];

        $request = (new ServerRequest('POST', 'https://pay.yansongda.cn/alipay/notify'))->withParsedBody($form);

        $rocket = (new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'alipay-v3']]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testNoRequest(): void
    {
        $rocket = (new Rocket())->setParams([]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);
        self::expectExceptionMessage('参数异常: 支付宝回调参数不正确');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    /**
     * 生成模拟支付宝异步通知的 form 参数（用测试私钥按 V2 参数格式签名，密钥与测试租户支付宝公钥/公钥证书同属一对）.
     */
    private function makeSignedForm(array $extraParams = []): array
    {
        $form = array_merge([
            'app_id' => 'alipay_v3_test_app_id',
            'trade_no' => '2023122122001499160501589436',
            'out_trade_no' => '1703147160',
            'total_amount' => '0.01',
            'trade_status' => 'TRADE_SUCCESS',
            'sign_type' => 'RSA2',
        ], $extraParams);

        $value = filter_params($form, fn ($k, $v) => '' !== $v && 'sign' != $k && 'sign_type' != $k)->sortKeys()->toString();

        openssl_sign($value, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

        $form['sign'] = base64_encode($sign);

        return $form;
    }
}
