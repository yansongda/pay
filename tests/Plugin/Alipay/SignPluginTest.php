<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class SignPluginTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'alipay' => [
                'default' => [
                    'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCDRjOg5DnX+8L+rB8d2MbrQ30Z7JPM4hiDhawHSwQCQ7RlmQNpl6b/N6IrPLcPFC1uii179U5Il5xTZynfjkUyJjnHusqnmHskftLJDKkmGbSUFMAlOv+NlpUWMJ2A+VUopl+9FLyqcV+XgbaWizxU3LsTtt64v89iZ2iC16H6/6a3YcP+hDZUjiNGQx9cuwi9eJyykvcwhDkFPxeBxHbfwppsul+DYUyTCcl0Ltbga/mUechk5BksW6yPPwprYHQBXyM16Jc3q5HbNxh3660FyvUBFLuVWIBs6RtR2gZCa6b8rOtCkPQKhUKvzRMlgheOowXsWdk99GjxGQDK5W4XAgMBAAECggEAYPKnjlr+nRPBnnNfR5ugzH67FToyrU0M7ZT6xygPfdyijaXDb2ggXLupeGUOjIRKSSijDrjLZ7EQMkguFHvtfmvcoDTDFaL2zq0a3oALK6gwRGxOuzAnK1naINkmeOmqiqrUab+21emEv098mRGbLNEXGCgltCtz7SiRdo/pgIPZ1wHj4MH0b0K2bFG3xwr51EyaLXKYH4j6w9YAXXsTdvzcJ+eRE0Yq4uGPfkziqg8d0xXSEt90HmCGHKo4O2eh1w1IlBcHfK0F6vkeUAtrtAV01MU2bNoRU147vKFxjDOVBlY1nIZY/drsbiPMuAfSsodL0hJxGSYivbKTX4CWgQKBgQDd0MkF5AIPPdFC+fhWdNclePRw4gUkBwPTIUljMP4o+MhJNrHp0sEy0sr1mzYsOT4J20hsbw/qTnMKGdgy784bySf6/CC7lv2hHp0wyS3Es0DRJuN+aTyyONOKGvQqd8gvuQtuYJy+hkIoHygjvC3TKndX1v66f9vCr/7TS0QPywKBgQCXgVHERHP+CarSAEDG6bzI878/5yqyJVlUeVMG5OXdlwCl0GAAl4mDvfqweUawSVFE7qiSqy3Eaok8KHkYcoRlQmAefHg/C8t2PNFfNrANDdDB99f7UhqhXTdBA6DPyW02eKIaBcXjZ7jEXZzA41a/zxZydKgHvz4pUq1BdbU5ZQKBgHyqGCDgaavpQVAUL1df6X8dALzkuqDp9GNXxOgjo+ShFefX/pv8oCqRQBJTflnSfiSKAqU2skosdwlJRzIxhrQlFPxBcaAcl0VTcGL33mo7mIU0Bw2H1d4QhAuNZIbttSvlIyCQ2edWi54DDMswusyAhHxwz88/huJfiad1GLaLAoGASIweMVNuD5lleMWyPw2x3rAJRnpVUZTc37xw6340LBWgs8XCEsZ9jN4t6s9H8CZLiiyWABWEBufU6z+eLPy5NRvBlxeXJOlq9iVNRMCVMMsKybb6b1fzdI2EZdds69LSPyEozjkxdyE1sqH468xwv8xUPV5rD7qd83+pgwzwSJkCgYBrRV0OZmicfVJ7RqbWyneBG03r7ziA0WTcLdRWDnOujQ9orhrkm+EY2evhLEkkF6TOYv4QFBGSHfGJ0SwD7ghbCQC/8oBvNvuQiPWI8B+00LwyxXNrkFOxy7UfIUdUmLoLc1s/VdBHku+JEd0YmEY+p4sjmcRnlu4AlzLxkWUTTg==',
                    'app_public_cert_path' => __DIR__ . '/../../Cert/alipayAppCertPublicKey_2016082000295641.crt',
                    'alipay_public_cert_path' => __DIR__ . '/../../Cert/alipayCertPublicKey_RSA2.crt',
                    'alipay_root_cert_path' => __DIR__ . '/../../Cert/alipayRootCert.crt',
                ],
            ]
        ];
        Pay::config($config);
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }

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
