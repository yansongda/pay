<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\DownloadPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new DownloadPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 下载发票文件，缺少 `download_url` 参数');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
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

