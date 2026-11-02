<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Jsb\AddPayloadSignPlugin;
use Yansongda\Supports\Collection;

it('签名正常', function () {
    $plugin = new AddPayloadSignPlugin();
    $payload = ['outTradeNo' => 'YC202406170003', 'totalFee' => 0.01, 'proInfo' => '充值', 'backUrl' => 'http:\/\/127.0.0.1:8000\/epay\/return', 'createData' => '20240618', 'createTime' => '022522', 'bizDate' => '20240618', 'msgId' => '16253083-49c4-4142-8c56-997accf3d667', 'svrCode' => '', 'partnerId' => '6a13eab71c4f4b0aa4757eda6fc59710', 'channelNo' => 'm', 'publicKeyCode' => '00', 'version' => 'v1.0.0', 'charset' => 'utf-8', 'service' => 'atPay'];
    $sign = 'Bho3LZvuv6wrQUAk6EP5lpGTCf5nDA1KDQwJy5Cog6m9S3UMVqpn0AC8+rrv5va63z5zAC6aQ7qrVH1OQ3hCeEUhhGix5HRUNgs2lzCkpywQnNsjeuapAAmfzVnDfBncPv9HuZSfdGxCOPqlkaSxonSXbB5ZpUfXbH3QjQo2F2w=';
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection($payload));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result->getPayload()->get('sign'))->toBe($sign);
});

it('空 payload', function () {
    $plugin = new AddPayloadSignPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection());

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少支付必要参数。可能插件用错顺序，应该先使用 `业务插件`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少商户私钥证书路径', function () {
    $plugin = new AddPayloadSignPlugin();
    $payload = ['outTradeNo' => 'YC202406170003', 'totalFee' => 0.01, 'proInfo' => '充值', 'backUrl' => 'http:\/\/127.0.0.1:8000\/epay\/return', 'createData' => '20240618', 'createTime' => '022522', 'bizDate' => '20240618', 'msgId' => '16253083-49c4-4142-8c56-997accf3d667', 'svrCode' => '', 'partnerId' => '6a13eab71c4f4b0aa4757eda6fc59710', 'channelNo' => 'm', 'publicKeyCode' => '00', 'version' => 'v1.0.0', 'charset' => 'utf-8', 'service' => 'atPay'];
    $sign = 'Bho3LZvuv6wrQUAk6EP5lpGTCf5nDA1KDQwJy5Cog6m9S3UMVqpn0AC8+rrv5va63z5zAC6aQ7qrVH1OQ3hCeEUhhGix5HRUNgs2lzCkpywQnNsjeuapAAmfzVnDfBncPv9HuZSfdGxCOPqlkaSxonSXbB5ZpUfXbH3QjQo2F2w=';
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection($payload));

    /** @var JsbConfig $config */
    $config = AddPayloadSignPlugin::getProviderConfig('jsb');
    expect($config)->toBeInstanceOf(JsbConfig::class);
    $config->setMchSecretCertPath('');
    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result->getPayload()->get('sign'))->toBe($sign);
})->throws(InvalidConfigException::class, '配置异常: 缺少江苏银行配置 -- [mch_secret_cert_path]', Exception::CONFIG_JSB_INVALID);

