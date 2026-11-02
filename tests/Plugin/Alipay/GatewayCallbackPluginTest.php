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
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

uses(GatewayCallbackPluginTestHelpers::class);

it('网关验签成功', function () {
    $plugin = new GatewayCallbackPlugin();
    $form = $this->makeSignedForm(extra: ['_config' => 'alipay-v3', '_action' => 'gw']);

    $result = $plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);

    $response = $result->getDestination();
    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getStatusCode())->toBe(200);
    expect($response->getHeaderLine('Content-Type'))->toBe('text/xml;charset=utf-8');

    $body = (string) $response->getBody();
    expect($body)->toContain('<success>true</success>');
    expect($body)->toContain('<sign_type>RSA2</sign_type>');

    // 应用公钥主体（剥离 PEM 头尾与换行的裸公钥串）应出现在应答中
    $appPublicKey = $this->appPublicKey();
    expect($body)->toContain($appPublicKey);

    // 用 fixture 应用公钥证书还原签名：签名内容必须是 `<response>` 内部文本原文
    $inner = '<success>true</success><biz_content>'.$appPublicKey.'</biz_content>';
    preg_match('/<sign>(.*?)<\/sign>/', $body, $matches);
    expect($matches)->toHaveKey(1);
    $sign = base64_decode($matches[1], true);
    expect($sign)->not->toBeFalse();
    expect(openssl_verify($inner, $sign, openssl_pkey_get_public(file_get_contents(__DIR__.'/../../Cert/alipay-v3/alipay_public_cert_test.crt')), OPENSSL_ALGO_SHA256))->toBe(1);
});

it('验签失败', function () {
    $plugin = new GatewayCallbackPlugin();
    $form = $this->makeSignedForm();
    $form['sign'] = 'invalid-sign';

    $result = $plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

    $response = $result->getDestination();
    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($response->getStatusCode())->toBe(200);

    $body = (string) $response->getBody();
    expect($body)->toContain('<success>false</success>');
    expect($body)->toContain('VERIFY_FAILED');
});

it('sign_type 参与验签', function () {
    $plugin = new GatewayCallbackPlugin();
    $form = $this->makeSignedForm();
    $form['sign_type'] = 'XXX';

    $result = $plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

    $response = $result->getDestination();
    expect($response)->toBeInstanceOf(ResponseInterface::class);

    $body = (string) $response->getBody();
    expect($body)->toContain('<success>false</success>');
    expect($body)->toContain('VERIFY_FAILED');
});

it('不校验网关事件类型', function () {
    $plugin = new GatewayCallbackPlugin();
    $form = $this->makeSignedForm(overrides: [
        'biz_content' => '<XML><MsgType>event</MsgType><EventType>follow</EventType></XML>',
    ]);

    $result = $plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);

    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);

    $destination = $result->getDestination();
    expect($destination)->toBeInstanceOf(Collection::class);
    expect($destination)->toBe($result->getPayload());
});

it('biz_content 非法 XML', function () {
    $plugin = new GatewayCallbackPlugin();
    $form = $this->makeSignedForm(overrides: ['biz_content' => 'not-xml']);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_ALIPAY_GW_BIZ_CONTENT_INVALID);

    $plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);
});

trait GatewayCallbackPluginTestHelpers
{
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
