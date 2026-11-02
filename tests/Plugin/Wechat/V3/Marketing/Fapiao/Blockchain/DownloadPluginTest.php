<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\DownloadPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new DownloadPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 下载发票文件，缺少 `download_url` 参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new DownloadPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_url' => 'https://pay.yansongda.cn?token=123',
        'appid' => '1111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => 'https://pay.yansongda.cn?token=123&appid=1111',
    ]);
});

