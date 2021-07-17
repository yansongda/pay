<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class SignPluginTest extends TestCase
{
    public function testNormal()
    {
        $payload = [
            "app_id" => "2016082000295641",
            "method" => "alipay.trade.query",
            "format" => "JSON",
            "return_url" => "http://127.0.0.1:8000/alipay/verify",
            "charset" => "utf-8",
            "sign_type" => "RSA2",
            "timestamp" => "2021-06-07 21:54:50",
            "version" => "1.0",
            "app_cert_sn" => "fb5e86cfb784de936dd3594e32381cf8",
            "alipay_root_cert_sn" => "687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6",
            "biz_content" => ['out_trade_no' => "yansongda-1622986519"],
        ];
        $sign = "QMh6CzKWIt5yIYCrYrMdC2/Mt+4lTNEaPN0biIZPuiWzgTS7pyIYFOmb+dEi70X5q9UaCBlejwwwTEzRtfIjudPu/mIrlpnwsN8mEhDjyZihmgb/wCZy+kR0OIwvZjTd/3AuALIcwDbhZqDwssZAOTlco4eE7WosEdsob52OfCBAn0ZEf/9zZk5+FSbL8xbwO9hTlspl5ArgFBf9RryBxAviC09Nr5eSNdwYBIyOUdLtEVHBuHHvwa4UfiCFe0SyDFVQODgoz3Mjcs5d4RmJqKNLorkN8dHzjzlAnCR07EHsMzV4ivNG703hReHMyazPDuaWBg11/spMJUNUF/tEBQ==";

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $plugin = new SignPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($sign, $result->getPayload()->get('sign'));
    }

    public function testUnderlineParams()
    {
        $payload = [
            "app_id" => "2016082000295641",
            "method" => "alipay.trade.query",
            "format" => "JSON",
            "return_url" => "http://127.0.0.1:8000/alipay/verify",
            "charset" => "utf-8",
            "sign_type" => "RSA2",
            "timestamp" => "2021-06-07 21:54:50",
            "version" => "1.0",
            "app_cert_sn" => "fb5e86cfb784de936dd3594e32381cf8",
            "alipay_root_cert_sn" => "687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6",
            "biz_content" => ['out_trade_no' => "yansongda-1622986519", '_method' => 'get', '_ignore' => true],
        ];
        $sign = "QMh6CzKWIt5yIYCrYrMdC2/Mt+4lTNEaPN0biIZPuiWzgTS7pyIYFOmb+dEi70X5q9UaCBlejwwwTEzRtfIjudPu/mIrlpnwsN8mEhDjyZihmgb/wCZy+kR0OIwvZjTd/3AuALIcwDbhZqDwssZAOTlco4eE7WosEdsob52OfCBAn0ZEf/9zZk5+FSbL8xbwO9hTlspl5ArgFBf9RryBxAviC09Nr5eSNdwYBIyOUdLtEVHBuHHvwa4UfiCFe0SyDFVQODgoz3Mjcs5d4RmJqKNLorkN8dHzjzlAnCR07EHsMzV4ivNG703hReHMyazPDuaWBg11/spMJUNUF/tEBQ==";

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $plugin = new SignPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($sign, $result->getPayload()->get('sign'));
    }
}
