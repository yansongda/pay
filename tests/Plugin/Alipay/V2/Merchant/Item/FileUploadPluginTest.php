<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Merchant\Item\FileUploadPlugin;

it('正常', function () {
    $plugin = new FileUploadPlugin();
    $picUrl = 'https://cdn.jsdelivr.net/gh/yansongda/pay/web/public/images/pay.jpg';
    $fileContent = file_get_contents($picUrl);
    $rocket = (new Rocket())
        ->setParams([
            '_multipart' => [
                [
                    'name' => 'file_content',
                    'contents' => $fileContent,
                    'filename' => basename($picUrl),
                ],
            ],
        ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $params = $result->getParams();
    expect($params)->toHaveKey('_multipart');
    expect($params['_multipart'][0]['filename'])->toEqual(basename($picUrl));
    expect($params['_multipart'][0]['contents'])->toEqual($fileContent);
});

