<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2;

use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadSignaturePluginTest extends TestCase
{
    protected AddPayloadSignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadSignaturePlugin();
    }

    public function testSignNormal()
    {
        $payload = [
            "app_id" => "9021000122682882",
            "method" => "alipay.trade.query",
            "format" => "JSON",
            "return_url" => "http://hk.ysdor.cn/index.php",
            "charset" => "utf-8",
            "sign_type" => "RSA2",
            "timestamp" => "2023-12-21 06:54:37",
            "version" => "1.0",
            "notify_url" => "http://hk.ysdor.cn/index.php",
            "app_cert_sn" => "e90dd23a37c5c7b616e003970817ff82",
            "alipay_root_cert_sn" => "687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6",
            "biz_content" => "{\"out_trade_no\":\"1703141270\"}",
        ];
        $sign = "FrzUwXziVWK2x5glZkGoQ6dGR87O33APv3D/lWjVqPrFnMwcYjOYyln1B1bw5VuOwokLRmqcwWuTumBg8Q+n4FkVR3DMCvL8ywTBp5+6pRAETfhFop4m+r6jI+dgOavwyoZ69CWCzCcEyb0vMjgrV2eoSI4bgLjJyPcGpZ+iDBTSvE6eVxs05bUjainAOB5NnAvME06nkaxGLB4qwF9c3SQoiZt7QcMRdyy6mRItZxRaEjMm6qX8dfPuuquJC4wpTRk8PQeu5EMR4yyc1atxso3CMovjkGr5LzDtqLNseyqqorYW+Gfu3ugHSs9aqkwAAysg79O6TsvuFa9OHq64Tg==";

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($sign, $result->getPayload()->get('sign'));
    }
}
