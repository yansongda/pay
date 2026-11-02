<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\GatewayCallbackPlugin;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use Yansongda\Pay\Tests\Support\Traits\AlipayGatewayCallbackHelpers;

uses(AlipayGatewayCallbackHelpers::class);

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
    expect(openssl_verify($inner, $sign, openssl_pkey_get_public(file_get_contents(cert_path('alipay-v3/alipay_public_cert_test.crt'))), OPENSSL_ALGO_SHA256))->toBe(1);
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
    // 组串保留 `sign_type`：签名基于 `sign_type=RSA2` 生成，请求篡改后验签必须失败（走 VERIFY_FAILED 分支）.
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
    // 非 verifygw 网关消息：验签通过后原样透传（payload 即 destination），ack 由业务侧处理.
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

    $plugin->assembly((new Rocket())->setParams($form), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_ALIPAY_GW_BIZ_CONTENT_INVALID);
