<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Qra;

use Yansongda\Pay\Plugin\Unipay\Qra\CallbackPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testReturnCallback()
    {
        $input = [
            "charset" => "UTF-8",
            "code" => "9999999",
            "err_code" => "NOAUTH",
            "err_msg" => "此商家涉嫌违规，收款功能已被限制，暂无法支付。商家可以登录微信商户平台/微信支付商家助手小程序查看原因和解决方案。",
            "mch_id" => "QRA29045311KKR1",
            "need_query" => "N",
            "nonce_str" => "UhxOr4kzerPGku9wCaVQyfd1zisoAnAm",
            "result_code" => "1",
            "sign" => "4B9B2AA73A05CBC32CFDCB4456E12EBA",
            "sign_type" => "MD5",
            "status" => "0",
            "transaction_id" => "95516000379952690603566602920171",
            "version" => "2.0",
            '_config' => 'qra',
        ];

        $rocket = new Rocket();
        $rocket->setParams($input);

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket;});

        self::assertNotEmpty($result->getPayload()->all());
    }
}
