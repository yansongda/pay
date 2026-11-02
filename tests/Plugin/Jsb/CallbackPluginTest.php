<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Jsb\CallbackPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new CallbackPlugin();
    $payload = [
        'partnerId' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'orderStatus' => '1',
        'totalFee' => '0.02',
        'outTradeNo' => 'RC240613164110030316',
        'orderNo' => '20240613164114400729509',
        'field1' => '2',
        'field2' => '',
        'field3' => '20240613164139|20240613164134400800219',
        'signType' => 'RSA',
        'sign' => 'DPKX4mZAVd/LwMDOt1OJgryBuPeH78y7B78smze+m+vvzae5MBf0O3BoTvVJQHD/RPVftHVvnYHeKvIjCC2bCrxoY9Sv2N8Hbr5HfjIikk0a2qaIQp6TTvecMP9JitzSuZP+sih+uxMkRM5Nrg8weGbePaQ6nODNWiSGDhV+Jq0=',
    ];
    $request = new ServerRequest('POST', 'http://localhost');
    $request = $request->withParsedBody($payload);

    $rocket = new Rocket();
    $rocket->setParams(['request' => Collection::wrap($request->getParsedBody())]);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('error sign', function () {
    $plugin = new CallbackPlugin();
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);
    $payload = [
        'partnerId' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'orderStatus' => '1',
        'totalFee' => '0.02',
        'outTradeNo' => 'RC240613164110030315',
        'orderNo' => '20240613164114400729509',
        'field1' => '2',
        'field2' => '',
        'field3' => '20240613164139|20240613164134400800219',
        'signType' => 'RSA',
        'sign' => 'DPKX4mZAVd/LwMDOt1OJgryBuPeH78y7B78smze+m+vvzae5MBf0O3BoTvVJQHD/RPVftHVvnYHeKvIjCC2bCrxoY9Sv2N8Hbr5HfjIikk0a2qaIQp6TTvecMP9JitzSuZP+sih+uxMkRM5Nrg8weGbePaQ6nODNWiSGDhV+Jq0=',
    ];
    $request = new ServerRequest('POST', 'http://localhost');
    $request = $request->withParsedBody($payload);

    $rocket = new Rocket();
    $rocket->setParams(['request' => Collection::wrap($request->getParsedBody())]);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('empty sign', function () {
    $plugin = new CallbackPlugin();
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);
    $payload = [
        'partnerId' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'orderStatus' => '1',
        'totalFee' => '0.02',
        'outTradeNo' => 'RC240613164110030316',
        'orderNo' => '20240613164114400729509',
        'field1' => '2',
        'field2' => '',
        'field3' => '20240613164139|20240613164134400800219',
        'signType' => 'RSA',
        'sign' => '',
    ];
    $request = new ServerRequest('POST', 'http://localhost');
    $request = $request->withParsedBody($payload);

    $rocket = new Rocket();
    $rocket->setParams(['request' => Collection::wrap($request->getParsedBody())]);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('error request type', function () {
    $plugin = new CallbackPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);
    $payload = [];
    $request = new ServerRequest('POST', 'http://localhost');
    $request = $request->withParsedBody($payload);

    $rocket = new Rocket();
    $rocket->setParams(['request' => $request->getParsedBody()]);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

it('missing epay public cert path', function () {
    $plugin = new CallbackPlugin();
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_JSB_INVALID);

    /** @var JsbConfig $config */
    $config = CallbackPlugin::getProviderConfig('jsb');
    expect($config)->toBeInstanceOf(JsbConfig::class);
    $config->setJsbPublicCertPath('');
    $payload = [
        'partnerId' => '6a13eab71c4f4b0aa4757eda6fc59710',
        'orderStatus' => '1',
        'totalFee' => '0.02',
        'outTradeNo' => 'RC240613164110030316',
        'orderNo' => '20240613164114400729509',
        'field1' => '2',
        'field2' => '',
        'field3' => '20240613164139|20240613164134400800219',
        'signType' => 'RSA',
        'sign' => 'DPKX4mZAVd/LwMDOt1OJgryBuPeH78y7B78smze+m+vvzae5MBf0O3BoTvVJQHD/RPVftHVvnYHeKvIjCC2bCrxoY9Sv2N8Hbr5HfjIikk0a2qaIQp6TTvecMP9JitzSuZP+sih+uxMkRM5Nrg8weGbePaQ6nODNWiSGDhV+Jq0=',
    ];
    $request = new ServerRequest('POST', 'http://localhost');
    $request = $request->withParsedBody($payload);

    $rocket = new Rocket();
    $rocket->setParams(['request' => Collection::wrap($request->getParsedBody())]);

    $result = $plugin->assembly($rocket, function ($rocket) {return $rocket; });

    expect($result->getPayload()->all())->not->toBeEmpty();
});

