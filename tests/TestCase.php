<?php

namespace Yansongda\Pay\Tests;

use Yansongda\Pay\Pay;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $config = [
            'alipay' => [
                'default' => [
                    'app_id' => 'yansongda',
                    'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCDRjOg5DnX+8L+rB8d2MbrQ30Z7JPM4hiDhawHSwQCQ7RlmQNpl6b/N6IrPLcPFC1uii179U5Il5xTZynfjkUyJjnHusqnmHskftLJDKkmGbSUFMAlOv+NlpUWMJ2A+VUopl+9FLyqcV+XgbaWizxU3LsTtt64v89iZ2iC16H6/6a3YcP+hDZUjiNGQx9cuwi9eJyykvcwhDkFPxeBxHbfwppsul+DYUyTCcl0Ltbga/mUechk5BksW6yPPwprYHQBXyM16Jc3q5HbNxh3660FyvUBFLuVWIBs6RtR2gZCa6b8rOtCkPQKhUKvzRMlgheOowXsWdk99GjxGQDK5W4XAgMBAAECggEAYPKnjlr+nRPBnnNfR5ugzH67FToyrU0M7ZT6xygPfdyijaXDb2ggXLupeGUOjIRKSSijDrjLZ7EQMkguFHvtfmvcoDTDFaL2zq0a3oALK6gwRGxOuzAnK1naINkmeOmqiqrUab+21emEv098mRGbLNEXGCgltCtz7SiRdo/pgIPZ1wHj4MH0b0K2bFG3xwr51EyaLXKYH4j6w9YAXXsTdvzcJ+eRE0Yq4uGPfkziqg8d0xXSEt90HmCGHKo4O2eh1w1IlBcHfK0F6vkeUAtrtAV01MU2bNoRU147vKFxjDOVBlY1nIZY/drsbiPMuAfSsodL0hJxGSYivbKTX4CWgQKBgQDd0MkF5AIPPdFC+fhWdNclePRw4gUkBwPTIUljMP4o+MhJNrHp0sEy0sr1mzYsOT4J20hsbw/qTnMKGdgy784bySf6/CC7lv2hHp0wyS3Es0DRJuN+aTyyONOKGvQqd8gvuQtuYJy+hkIoHygjvC3TKndX1v66f9vCr/7TS0QPywKBgQCXgVHERHP+CarSAEDG6bzI878/5yqyJVlUeVMG5OXdlwCl0GAAl4mDvfqweUawSVFE7qiSqy3Eaok8KHkYcoRlQmAefHg/C8t2PNFfNrANDdDB99f7UhqhXTdBA6DPyW02eKIaBcXjZ7jEXZzA41a/zxZydKgHvz4pUq1BdbU5ZQKBgHyqGCDgaavpQVAUL1df6X8dALzkuqDp9GNXxOgjo+ShFefX/pv8oCqRQBJTflnSfiSKAqU2skosdwlJRzIxhrQlFPxBcaAcl0VTcGL33mo7mIU0Bw2H1d4QhAuNZIbttSvlIyCQ2edWi54DDMswusyAhHxwz88/huJfiad1GLaLAoGASIweMVNuD5lleMWyPw2x3rAJRnpVUZTc37xw6340LBWgs8XCEsZ9jN4t6s9H8CZLiiyWABWEBufU6z+eLPy5NRvBlxeXJOlq9iVNRMCVMMsKybb6b1fzdI2EZdds69LSPyEozjkxdyE1sqH468xwv8xUPV5rD7qd83+pgwzwSJkCgYBrRV0OZmicfVJ7RqbWyneBG03r7ziA0WTcLdRWDnOujQ9orhrkm+EY2evhLEkkF6TOYv4QFBGSHfGJ0SwD7ghbCQC/8oBvNvuQiPWI8B+00LwyxXNrkFOxy7UfIUdUmLoLc1s/VdBHku+JEd0YmEY+p4sjmcRnlu4AlzLxkWUTTg==',
                    'app_public_cert_path' => __DIR__ . '/Cert/alipayAppCertPublicKey_2016082000295641.crt',
                    'alipay_public_cert_path' => __DIR__ . '/Cert/alipayCertPublicKey_RSA2.crt',
                    'alipay_root_cert_path' => __DIR__ . '/Cert/alipayRootCert.crt',
                ],
            ],
            'wechat' => [
                'default' => [
                    'app_id' => 'yansongda',
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                        'yansongda' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    'mode' => Pay::MODE_NORMAL,
                ],
                'service_provider' => [
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    'sub_mp_app_id' => 'wx55955316af4ef15',
                    'sub_app_id' => 'wx55955316af4ef16',
                    'sub_mini_app_id' => 'wx55955316af4ef17',
                    'sub_mch_id' => '1600314070',
                    'mode' => Pay::MODE_SERVICE,
                ],
                'service_provider2' => [
                    'mp_app_id' => 'wx55955316af4ef18',
                    'mch_id' => '1600314071',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    'sub_mp_app_id' => 'wx55955316af4ef19',
                    'sub_app_id' => 'wx55955316af4ef20',
                    'sub_mini_app_id' => 'wx55955316af4ef21',
                    'sub_mch_id' => '1600314072',
                    'mode' => Pay::MODE_SERVICE,
                ],
                'service_provider3' => [
                    'mp_app_id' => 'wx55955316af4ef18',
                    'mch_id' => '1600314071',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    'sub_mp_app_id' => 'wx55955316af4ef19',
                    'sub_app_id' => 'wx55955316af4ef20',
                    'sub_mini_app_id' => '',
                    'sub_mch_id' => '1600314072',
                    'mode' => Pay::MODE_SERVICE,
                ]
            ]
        ];
        Pay::config($config);
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }
}
