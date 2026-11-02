<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Virtual\AddPayloadSignaturePlugin;
use Yansongda\Supports\Collection;

it('正常组装', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"openid":"oUpF8muMJAaName"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance',
        '_body' => $body,
        'access_token' => 'test_access_token',
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();
    $url = $resultPayload->get('_url');

    expect($url)->toContain('access_token=test_access_token');
    expect($url)->toContain('pay_sig=');
    expect($url)->not->toContain('signature=');

    // verify pay_sig value
    $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
    $expectedPaySig = hash_hmac('sha256', '/xpay/query_user_balance&'.$body, $config->getVirtualPay()->getAppKey());
    expect($url)->toContain('pay_sig='.$expectedPaySig);

    // paySig 可作为 payload 独立字段访问
    expect($resultPayload->get('paySig'))->toEqual($expectedPaySig);
    expect($resultPayload->get('signature'))->toBeNull();
});

it('携带会话密钥组装', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"openid":"oUpF8muMJAaName"}';
    $sessionKey = 'test_session_key_value';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance',
        '_body' => $body,
        'access_token' => 'test_access_token',
        '_session_key' => $sessionKey,
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();
    $url = $resultPayload->get('_url');

    expect($url)->toContain('access_token=test_access_token');
    expect($url)->toContain('pay_sig=');
    expect($url)->toContain('signature=');

    // verify signature value
    $expectedSignature = hash_hmac('sha256', $body, $sessionKey);
    expect($url)->toContain('signature='.$expectedSignature);

    // paySig 和 signature 可作为 payload 独立字段访问
    expect($resultPayload->get('paySig'))->not->toBeNull();
    expect($resultPayload->get('signature'))->toEqual($expectedSignature);
});

it('组装缺少 app_key', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance',
        '_body' => '{"openid":"oUpF8muMJAaName"}',
        'access_token' => 'test_access_token',
    ]);

    $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
    $config->getVirtualPay()->setAppKey(null);

    $rocket = (new Rocket())->setPayload($payload);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidConfigException::class, '配置异常: 缺少微信虚拟支付配置 -- [virtual_pay.app_key]', Exception::CONFIG_WECHAT_INVALID);

it('沙箱环境组装', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"openid":"oUpF8muMJAaName"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance',
        '_body' => $body,
        'access_token' => 'test_access_token',
        "env" => 1,
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();
    $url = $resultPayload->get('_url');

    $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
    $sandboxAppKey = $config->getVirtualPay()->getAppKey(1);
    $expectedPaySig = hash_hmac('sha256', '/xpay/query_user_balance&'.$body, $sandboxAppKey);
    expect($url)->toContain('pay_sig='.$expectedPaySig);
});

it('URL 已有查询参数时组装', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"openid":"oUpF8muMJAaName"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance?existing_param=value',
        '_body' => $body,
        'access_token' => 'test_access_token',
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();
    $url = $resultPayload->get('_url');

    expect($url)->toContain('existing_param=value');
    expect($url)->toContain('access_token=test_access_token');
    expect($url)->toContain('pay_sig=');
});

it('客户端签名无 access token 组装', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"buyQuantity":1,"productId":"test_product","goodsPrice":10,"offerId":"1234567890","currencyType":"CNY"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'requestVirtualPayment',
        '_body' => $body,
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    // 客户端签名场景不传 access_token 不应抛异常
    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();
    $url = $resultPayload->get('_url');

    expect($url)->toContain('pay_sig=');
    expect($url)->not->toContain('access_token=');

    // paySig 可作为 payload 独立字段访问
    expect($resultPayload->get('paySig'))->not->toBeNull();
});

it('服务端组装缺少 access token', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance',
        '_body' => '{"openid":"oUpF8muMJAaName"}',
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    // 服务端 API 场景缺少 access_token 且未配置 app_secret 应抛异常

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 微信虚拟支付缺少 access_token，请配置 [virtual_pay.app_secret] 以自动获取，或在参数中手动传入 access_token', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('服务端组装自动获取 access token', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $config = AddPayloadSignaturePlugin::getProviderConfig('wechat', []);
    expect($config)->toBeInstanceOf(WechatConfig::class);
    $config->getVirtualPay()->setAppSecret('test_app_secret');
    $config->getVirtualPay()->setAccessToken('cached_virtual_token_123');
    $config->getVirtualPay()->setAccessTokenExpiry(time() + 3600);

    Pay::config([
        'wechat' => [
            'default' => $config->toArray(),
        ],
        '_force' => true,
    ]);

    $body = '{"openid":"oUpF8muMJAaName"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/present_currency',
        '_body' => $body,
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    // 服务端 API 场景缺少 access_token 但配置了 app_secret，应自动获取（缓存命中，不发起真实请求）
    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();
    $url = $resultPayload->get('_url');

    expect($url)->toContain('access_token=cached_virtual_token_123');
    expect($url)->toContain('pay_sig=');

    // paySig 可作为 payload 独立字段访问
    expect($resultPayload->get('paySig'))->not->toBeNull();
});

it('客户端签名返回 sign_data 字符串', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"buyQuantity":1,"productId":"test_product","goodsPrice":10,"offerId":"1234567890","currencyType":"CNY"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => 'requestVirtualPayment',
        '_body' => $body,
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();

    // signData 应与参与签名计算的 body 逐字节一致
    expect($resultPayload->get('signData'))->toBe($body);

    // _body 不应被修改
    expect($resultPayload->get('_body'))->toBe($body);
});

it('服务端组装无 sign_data 字段', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"openid":"oUpF8muMJAaName"}';
    $payload = new Collection([
        '_method' => 'POST',
        '_url' => '/xpay/query_user_balance',
        '_body' => $body,
        'access_token' => 'test_access_token',
    ]);
    $rocket = (new Rocket())->setPayload($payload);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $resultPayload = $result->getPayload();

    // 服务端 API 场景不应有 signData 字段
    expect($resultPayload->get('signData'))->toBeNull();
});

