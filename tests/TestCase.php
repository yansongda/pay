<?php

namespace Yansongda\Pay\Tests;

use Hyperf\Pimple\ContainerFactory;
use Hyperf\Utils\ApplicationContext;
use Yansongda\Pay\Pay;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $config = [
            'alipay' => [
                'default' => [
                    'app_id' => '9021000122682882',
                    'app_secret_cert' => 'MIIEpAIBAAKCAQEApSA9oxvcqfbgpgkxXvyCpnxaR6TPaEMh/ij+PhF8180zL82ic4whkrRlcu1Y179AKEZNar71Ugi37fKcXWLerjPOeb8WHnZgNG19gkAcOIqZPRPpJ1eRtwKEclIzt+j3H/wgXWkD7BTr61RjuAcviyvDVbAJ/TPlMqXdJFIuJwZblN2WblIv+4Dm1iPOB+fVCU3rsgg4eajf3HrZ7sq6fBhQhO5krDmIIYGsFZ+fohEgnLkBaF0gqNUb5Yb4PBfaEcu8Hcwq+XyBSMOVOIABRPQVDedW2sE/2NsLkR62DaEe/Ri9VUDJe0pE39P+X22DicJ3E3yrxvdioMnLtDqEuwIDAQABAoIBAQCSHZ1tH9J7c8IGKkxNyROzToZ0rxn5IK6LwKp5MfBO5X1N56DArldnAcpjkDL1dn7HJK6Mrr1WAfD/1ZcX680wSReEE9r2ybkHq3tMLn7KaZp/uYavEYYXc1rP7n1lV/iVjPz2q16VIU5Bx0MWLQWdGPSYdlXggHNoBe1RnobIcCGOVe9HlzCBtWzGpCZvMlqRbCuWAdp14aCkaJqpRxG4PY9Kd/NzELvhnCd9k8e7G2qcwx6gAoXN8OXO8jmZg/6fOvFnrGl6CBp8sioe5F3R023fDum546IqS8EZdCl5T0gW/boTbSV8luitab65xBO3PmUI+V2OEFCL6WcJxawBAoGBAOZoft6/LatdoXzr8vh+rKzacUHw246fpacbgx0B5DDymM7hbhXbY/NoCWPgBJtV3XI3DtMJ5yvlEVDQvPfbSHRPx2XQknwrM7ly2SLbaC+tuhcvoG6F1RLWFx+y/583seSlVNuWC9KdpLTKzo8wl8Z4/kheLTBxTxL20NZu79XBAoGBALd3fNoXk5V+T16hnSinPtt2NEsZpn+4w07DikzcpdyjCL5PYjp/BppmX3xly96fCZh3MO3Vkuya1xgauMzxVKQlR/aD5yVmsqK7wxNTY1ZQM74B44/4Mks/8MG2r7o3DElA4/qIeMP4CwkWmYcuij7npm2bgIqFzS+4aGZfDRF7AoGAKMO2Jpy2bMo9BwgLzdFDpbVkMmF1xu8R9NXWRayO/eX+CSQzQOS281qlxqjcx8rSSiHZmpb28notrRmxRTzjvchbo/TZ5eQS262pIxSkg0L+WJnRjZxaDWIZZz9ZIIdPDv/9WnhakSHZAS+cihLz12aSvqUC4744WkeWvUmVX0ECgYAGLDoCKHrps7c96tgbzwy5W4/E2xcUAwZnNwMHNQFLnBymMouOhkmVlk4uJEqosdcjzxbRWbc4yLjl8bg4BQKhBzQVojh7tKnb+c9Fbi/QbqBfCzc519LxXzRdgCUHceSy7kD9Y+wUQ9szMhR2TOWP2kFqPKolfvz5Vw4EK7yH0wKBgQDerq9Pthbii7lNt528/q0cH9vOMn9z76o6jMMea9EibclVHtdcQBWLOn8Yw97k+WSXYGuUrQUWWQbyabZqWkkS4cEjJf5/DiwOuYdNVXg7FK56ucTczBA7lR4dnunPW6U1HbSWf0Cn4Y/cl/z7B5QBSQt0W38IYHSaf6/sqsV6SA==',
                    'app_auth_token' => '',
                    'app_public_cert_path' => __DIR__ . '/Cert/alipayAppPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__ . '/Cert/alipayPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__ . '/Cert/alipayRootCert.crt',
                    'notify_url' => 'https://pay.yansongda.cn',
                    'return_url' => 'https://pay.yansongda.cn',
                ],
            ],
            'wechat' => [
                'default' => [
                    'app_id' => 'yansongda',
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key_v2' => 'yansongda',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'notify_url' => 'https://pay.yansongda.cn',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                        'yansongda' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    'mode' => Pay::MODE_NORMAL,
                ],
                'service_provider' => [
                    'app_id' => 'yansongdaa',
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key_v2' => 'yansongda',
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
                    'mch_secret_key_v2' => 'yansongda',
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
                    'mch_secret_key_v2' => 'yansongda',
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
                ],
                'service_provider4' => [
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key_v2' => 'yansongda',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    'sub_mp_app_id' => '',
                    'sub_app_id' => 'wx55955316af4ef16',
                    'sub_mini_app_id' => 'wx55955316af4ef17',
                    'sub_mch_id' => '1600314070',
                    'mode' => Pay::MODE_SERVICE,
                ],
                'empty_wechat_public_cert' => [
                    'app_id' => 'yansongda',
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key_v2' => 'yansongda',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => __DIR__.'/Cert/wechatAppPrivateKey.pem',
                    'mch_public_cert_path' => __DIR__.'/Cert/wechatAppPublicKey.pem',
                    'wechat_public_cert_path' => [],
                    'mode' => Pay::MODE_NORMAL,
                ],
            ],
            'unipay' => [
                'default' => [
                    'mch_id' => '777290058167151',
                    'mch_cert_path' => __DIR__.'/Cert/unipayAppCert.pfx',
                    'mch_cert_password' => '000000',
                    'unipay_public_cert_path' => __DIR__.'/Cert/unipayCertPublicKey.cer',
                    'return_url' => 'https://pay.yansongda.cn',
                    'notify_url' => 'https://pay.yansongda.cn',
                ],
                'sandbox' => [
                    'mch_id' => '777290058167151',
                    'mch_cert_path' => __DIR__.'/Cert/unipayAppCert.pfx',
                    'mch_cert_password' => '000000',
                    'unipay_public_cert_path' => __DIR__.'/Cert/unipayCertPublicKey.cer',
                    'return_url' => 'https://pay.yansongda.cn',
                    'notify_url' => 'https://pay.yansongda.cn',
                    'mode' => Pay::MODE_SANDBOX,
                ]
            ]
        ];

        // hyperf 单测时，未在 hyperf 框架内，所以 sdk 没有 container, 手动设置一个
        if (class_exists(ApplicationContext::class) && class_exists(ContainerFactory::class)) {
            ApplicationContext::setContainer((new ContainerFactory())());
        }

        Pay::config($config);
    }

    protected function tearDown(): void
    {
        Pay::clear();
    }
}
