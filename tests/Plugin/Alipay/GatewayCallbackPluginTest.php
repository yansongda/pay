<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\GatewayCallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

class GatewayCallbackPluginTest extends TestCase
{
    private GatewayCallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GatewayCallbackPlugin();
    }

    public function testVerifyGwSuccess(): void
    {
        $form = $this->makeSignedForm(extra: ['_config' => 'alipay-v3', '_action' => 'gw']);

        $result = $this->plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());

        $response = $result->getDestination();
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertInstanceOf(Response::class, $response);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/xml;charset=utf-8', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        self::assertStringContainsString('<success>true</success>', $body);
        self::assertStringContainsString('<sign_type>RSA2</sign_type>', $body);

        // 应用公钥主体（剥离 PEM 头尾与换行的裸公钥串）应出现在应答中
        $appPublicKey = $this->appPublicKey();
        self::assertStringContainsString($appPublicKey, $body);

        // 用 fixture 应用公钥证书还原签名：签名内容必须是 `<response>` 内部文本原文
        $inner = '<success>true</success><biz_content>'.$appPublicKey.'</biz_content>';
        preg_match('/<sign>(.*?)<\/sign>/', $body, $matches);
        self::assertArrayHasKey(1, $matches);
        $sign = base64_decode($matches[1], true);
        self::assertNotFalse($sign);
        self::assertSame(1, openssl_verify($inner, $sign, openssl_pkey_get_public(file_get_contents(__DIR__.'/../../Cert/alipay-v3/alipay_public_cert_test.crt')), OPENSSL_ALGO_SHA256));
    }

    public function testVerifySignFailed(): void
    {
        $form = $this->makeSignedForm();
        $form['sign'] = 'invalid-sign';

        $result = $this->plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

        $response = $result->getDestination();
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        self::assertStringContainsString('<success>false</success>', $body);
        self::assertStringContainsString('VERIFY_FAILED', $body);
    }

    /**
     * 组串保留 `sign_type`：签名基于 `sign_type=RSA2` 生成，请求篡改后验签必须失败（走 VERIFY_FAILED 分支）.
     */
    public function testSignTypeParticipatesInVerify(): void
    {
        $form = $this->makeSignedForm();
        $form['sign_type'] = 'XXX';

        $result = $this->plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

        $response = $result->getDestination();
        self::assertInstanceOf(ResponseInterface::class, $response);

        $body = (string) $response->getBody();
        self::assertStringContainsString('<success>false</success>', $body);
        self::assertStringContainsString('VERIFY_FAILED', $body);
    }

    /**
     * 非 verifygw 网关消息：验签通过后原样透传（payload 即 destination），ack 由业务侧处理.
     */
    public function testNonVerifyGwEventType(): void
    {
        $form = $this->makeSignedForm(overrides: [
            'biz_content' => '<XML><MsgType>event</MsgType><EventType>follow</EventType></XML>',
        ]);

        $result = $this->plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

        self::assertSame(NoHttpRequestDirection::class, $result->getDirection());

        $destination = $result->getDestination();
        self::assertInstanceOf(Collection::class, $destination);
        self::assertSame($result->getPayload(), $destination);
    }

    public function testBizContentInvalidXml(): void
    {
        $form = $this->makeSignedForm(overrides: ['biz_content' => 'not-xml']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_ALIPAY_GW_BIZ_CONTENT_INVALID);

        $this->plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);
    }

    /**
     * 生成模拟支付宝应用网关请求的 form 参数（用测试私钥按 verifygw 组串规则签名：仅剔除 `sign`，保留 `sign_type`，密钥与 alipay-v3 测试租户支付宝公钥证书同属一对）.
     *
     * @param array<string, string> $overrides 覆盖业务参数（在签名前应用，用于篡改组串场景）
     * @param array<string, string> $extra     追加不参与组串的 `_` 前缀参数（验证 `_` 前缀不污染组串）
     *
     * @return array<string, string>
     */
    private function makeSignedForm(array $overrides = [], array $extra = ['_config' => 'alipay-v3']): array
    {
        $form = array_merge([
            'service' => 'alipay.service.check',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'biz_content' => '<XML><AppId>alipay_v3_test_app_id</AppId><MsgType>event</MsgType><EventType>verifygw</EventType></XML>',
        ], $overrides);

        $value = filter_params(array_merge($form, $extra), fn ($k, $v) => '' !== $v && 'sign' != $k)
            ->sortKeys()
            ->toString();

        openssl_sign($value, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

        $form['sign'] = base64_encode($sign);

        return array_merge($form, $extra);
    }

    /**
     * 从 fixture 应用公钥证书提取应用公钥主体（剥离 PEM 头尾与换行的裸公钥串）.
     */
    private function appPublicKey(): string
    {
        $publicKey = openssl_pkey_get_public(file_get_contents(__DIR__.'/../../Cert/alipay-v3/alipay_public_cert_test.crt'));
        self::assertNotFalse($publicKey);

        $details = openssl_pkey_get_details($publicKey);
        self::assertIsArray($details);
        self::assertArrayHasKey('key', $details);

        return str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\r", "\n"], '', $details['key']);
    }
}
