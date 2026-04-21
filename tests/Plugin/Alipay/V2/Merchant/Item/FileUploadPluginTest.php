<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Merchant\Item;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Merchant\Item\FileUploadPlugin;
use Yansongda\Pay\Tests\TestCase;

class FileUploadPluginTest extends TestCase
{
    protected FileUploadPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new FileUploadPlugin();
    }

    public function testNormal()
    {
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

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $params = $result->getParams();
        $this->assertArrayHasKey('_multipart', $params);
        $this->assertEquals(basename($picUrl), $params['_multipart'][0]['filename']);
        $this->assertEquals($fileContent, $params['_multipart'][0]['contents']);
    }
}
