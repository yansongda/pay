<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Stubs\WechatTraitStub;

describe('request', function () {
    it('拼接 URL', function () {
        $config = WechatTraitStub::getProviderConfig('wechat');
        expect($config)->toBeInstanceOf(WechatConfig::class);

        $serviceConfig = new WechatConfig([
            'app_id' => 'yansongda',
            'mp_app_id' => 'wx55955316af4ef13',
            'mch_id' => '1600314069',
            'mini_app_id' => 'wx55955316af4ef14',
            'mch_secret_key_v2' => 'yansongda',
            'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
            'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
            'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
            'notify_url' => 'https://pay.yansongda.cn',
            'wechat_public_cert_path' => [
                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => cert_path('wechatAppPublicKey.pem'),
                'yansongda' => cert_path('wechatPublicKey.crt'),
            ],
            'sub_mch_id' => '1600314070',
            'mode' => PayMode::SERVICE,
        ]);

        expect(WechatTraitStub::getWechatUrl($config, new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');
        expect(WechatTraitStub::getWechatUrl($config, new Collection(['_url' => '/api/v1/yansongda'])))->toEqual('https://api.mch.weixin.qq.com/api/v1/yansongda');
        expect(WechatTraitStub::getWechatUrl($serviceConfig, new Collection(['_service_url' => '/api/v1/service/yansongda'])))->toEqual('https://api.mch.weixin.qq.com/api/v1/service/yansongda');
        expect(WechatTraitStub::getWechatUrl($serviceConfig, new Collection(['_url' => '/foo', '_service_url' => '/api/v1/service/yansongda'])))->toEqual('https://api.mch.weixin.qq.com/api/v1/service/yansongda');
        expect(WechatTraitStub::getWechatUrl(WechatTraitStub::getProviderConfig('wechat'), new Collection(['_url' => '/api/v1/yansongda'])))->toEqual('https://api.mch.weixin.qq.com/api/v1/yansongda');
        expect(WechatTraitStub::getWechatUrl($config, new Collection(['_url' => '/xpay/query_user_balance'])))->toEqual('https://api.weixin.qq.com/xpay/query_user_balance');
        expect(WechatTraitStub::getWechatUrl($config, new Collection(['_url' => '/cgi-bin/stable_token'])))->toEqual('https://api.weixin.qq.com/cgi-bin/stable_token');
        expect(WechatTraitStub::getWechatUrl($config, new Collection(['_url' => '/sns/jscode2session'])))->toEqual('https://api.weixin.qq.com/sns/jscode2session');

        WechatTraitStub::getWechatUrl($config, new Collection([]));
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_WECHAT_URL_MISSING);

    it('获取请求方法', function () {
        expect(WechatTraitStub::getWechatMethod(null))->toEqual('POST');
        expect(WechatTraitStub::getWechatMethod(new Collection(['_method' => 'get'])))->toEqual('GET');
        expect(WechatTraitStub::getWechatMethod(new Collection(['_method' => 'post'])))->toEqual('POST');
    });

    it('获取请求 body', function () {
        expect(WechatTraitStub::getWechatBody(new Collection(['_body' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');

        WechatTraitStub::getWechatBody(new Collection([]));
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_WECHAT_BODY_MISSING);
});

describe('sign', function () {
    it('获取 V3 签名', function () {
        $contents = "POST\n/v3/pay/transactions/h5\n1626493236\nQqtzdVzxavZeXag9G5mtfzbfzFMf89p6\n{\"out_trade_no\":1626493236,\"description\":\"yansongda 测试 - 1626493236\",\"amount\":{\"total\":1},\"scene_info\":{\"payer_client_ip\":\"127.0.0.1\",\"h5_info\":{\"type\":\"Wap\"}},\"appid\":\"wx55955316af4ef13\",\"mchid\":\"1600314069\",\"notify_url\":\"http:\/\/127.0.0.1:8000\/wechat\/notify\"}\n";

        expect(WechatTraitStub::getWechatSign(WechatTraitStub::getProviderConfig('wechat', []), $contents))->toEqual('KzIgMgiop3nQJNdBVR2Xah/JUwVBLDFFajyXPiSN8b8YAYEA4FuWfaCgFJ52+WFed+PhOYWx/ZPih4RaEuuSdYB8eZwYUx7RZGMQZk0bKCctAjjPuf4pJN+f/WsXKjPIy3diqF5x7gyxwSCaKWP4/KjsHNqgQpiC8q1uC5xmElzuhzSwj88LIoLtkAuSmtUVvdAt0Nz41ECHZgHWSGR32TfBo902r8afdaVKkFde8IoqcEJJcp6sMxdDO5l9R5KEWxrJ1SjsXVrb0IPH8Nj7e6hfhq7pucxojPpzsC+ZWAYvufZkAQx3kTiFmY87T+QhkP9FesOfWvkIRL4E6MP6ug==');

        // test config error
        $config1 = [
            'wechat' => [
                'default' => [
                    'mch_secret_cert' => '',
                ],
            ],
        ];

        Pay::config(array_merge($config1, ['_force' => true]));

        WechatTraitStub::getProviderConfig('wechat');
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);

    it('获取 V2 签名', function () {
        $params = ['name' => 'yansongda', 'age' => 29, 'foo' => ''];
        expect(WechatTraitStub::getWechatSignV2(WechatTraitStub::getProviderConfig('wechat', $params), $params))->toEqual('3213848AED2C380749FD1D559555881D');

        // test config error
        $config1 = [
            'wechat' => [
                'default' => array_merge((new WechatConfig([
                    'app_id' => 'yansongda',
                    'mp_app_id' => 'wx55955316af4ef13',
                    'mch_id' => '1600314069',
                    'mini_app_id' => 'wx55955316af4ef14',
                    'mch_secret_key_v2' => 'yansongda',
                    'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
                    'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
                    'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
                    'notify_url' => 'https://pay.yansongda.cn',
                    'wechat_public_cert_path' => [
                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => cert_path('wechatAppPublicKey.pem'),
                        'yansongda' => cert_path('wechatPublicKey.crt'),
                    ],
                    'mode' => PayMode::NORMAL,
                ]))->toArray(), ['mch_secret_key_v2' => '']),
            ],
        ];
        Pay::config(array_merge($config1, ['_force' => true]));

        WechatTraitStub::getWechatSignV2(WechatTraitStub::getProviderConfig('wechat'), []);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);
});

describe('verifySign', function () {
    it('V3 验签', function () {
        $response = new Response(
            200,
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'Ut3dG8cMx5W1lbSQhHay068F6khScuPQJM/Z9+suaaSkbYUspFRlkdp2VR/6w5UMvioN0EveSgfypQFVqmT6tI//cWrA1J9rlnKmZ+FgdCMqg7FQnpMRzc1Ap+3mZMtN9GrzYqp/UdgotX6HRfGL3hP8pG1YuijHNrL0QRS17bNYwZX8Mj3qLKUQRpqbfE+TC5yvzh1gEVPBFTwvZdZvXIQpjC/sB2QDSvo72CWgm4huh1h/kMzsrsO+wXXLqDfU01YX8aLbBrjvpcob50lc5XZ2WX5nBbpJXaRatIhBUmkR/ccrQhxWN7YqEobBGK/2DYhr6e6CvTgVdpZUUEcMFw==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode(['h5_url' => 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx16220223998099f898c5b24eed5c320000&package=4049184564'], JSON_UNESCAPED_SLASHES),
        );
        WechatTraitStub::verifyWechatSign($response, []);
        expect(true)->toBeTrue();

        $response = new Response(
            200,
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'Ut3dG8cMx5W1lbSQhHay068F6khScuPQJM/Z9+suaaSkbYUspFRlkdp2VR/6w5UMvioN0EveSgfypQFVqmT6tI//cWrA1J9rlnKmZ+FgdCMqg7FQnpMRzc1Ap+3mZMtN9GrzYqp/UdgotX6HRfGL3hP8pG1YuijHNrL0QRS17bNYwZX8Mj3qLKUQRpqbfE+TC5yvzh1gEVPBFTwvZdZvXIQpjC/sB2QDSvo72CWgm4huh1h/kMzsrsO+wXXLqDfU01YX8aLbBrjvpcob50lc5XZ2WX5nBbpJXaRatIhBUmkR/ccrQhxWN7YqEobBGK/2DYhr6e6CvTgVdpZUUEcMFw==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode(['h5_url' => 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx16220223998099f898c5b24eed5c320000&package=4049184564'], JSON_UNESCAPED_SLASHES),
        );
        $response->getBody()->read(10);
        WechatTraitStub::verifyWechatSign($response, []);
        expect(true)->toBeTrue();
    });

    it('V2 验签', function () {
        $destination = ['name' => 'yansongda', 'age' => 29, 'foo' => '', 'sign' => '3213848AED2C380749FD1D559555881D'];
        WechatTraitStub::verifyWechatSignV2(WechatTraitStub::getProviderConfig('wechat'), $destination);
        expect(true)->toBeTrue();

        $destination = ['name' => 'yansongda', 'age' => 29, 'foo' => ''];
        WechatTraitStub::verifyWechatSignV2(WechatTraitStub::getProviderConfig('wechat'), $destination);
    })->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

    it('V2 验签密钥为空', function () {
        $destination = ['name' => 'yansongda', 'age' => 29, 'foo' => '', 'sign' => 'aaa'];

        $config = WechatTraitStub::getProviderConfig('wechat');
        expect($config)->toBeInstanceOf(WechatConfig::class);
        $config->setMchSecretKeyV2('');

        WechatTraitStub::verifyWechatSignV2($config, $destination);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);

    it('V2 验签失败', function () {
        $destination = ['name' => 'yansongda', 'age' => 29, 'foo' => '', 'sign' => 'foo'];

        WechatTraitStub::verifyWechatSignV2(WechatTraitStub::getProviderConfig('wechat'), $destination);
    })->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);
});

describe('crypt', function () {
    it('加密内容', function () {
        $serialNo = '45F59D4DABF31918AFCEC556D5D2C6E376675D57';
        $contents = 'yansongda';
        $config = WechatTraitStub::getProviderConfig('wechat');

        expect($config)->toBeInstanceOf(WechatConfig::class);
        $result = WechatTraitStub::encryptWechatContents($contents, CertManager::wechatGetCertBySerial($config->getTenant(), $serialNo) ?? '');
        expect($result)->toBeString();
    });

    it('解密内容', function () {
        $encrypted = 'WIesmK+dSJycwdhTTkNmv0Lk2wb9o7NGODovccjhyotNnRkEeh+sxRK1gNSRNMJJgkQ30m4HwcuweSO24mehFeXVNTVAKFVef/3FlHnYDZfE1c3mCLToEef7e8J/Z8TwFH1ecn3t+Jk9ZaBpQKNHdQ0Q8jcL7AnL48h0D9BcZxDekPqX6hNnKfISoKSv4TXFcgvBLFeAe4Q3KM0Snq0N5IvI86D9xZqVg6mY+Gfz0782ymQFxflau6Qxx3mJ+0etHMocNuCdgctVH390XYYMc0u+V2FCJ5cU5h/M/AxzP9ayrEO4l0ftaxL6lP0HjifNrkPcAAb+q9I67UepKO9iGw==';

        $config = WechatTraitStub::getProviderConfig('wechat');

        expect(WechatTraitStub::decryptWechatContents($encrypted, $config))->toEqual('yansongda');
        expect(WechatTraitStub::decryptWechatContents('invalid', $config))->toBeNull();
    });

    it('解密资源', function () {
        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificate',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];

        $result = WechatTraitStub::decryptWechatResource($resource, WechatTraitStub::getProviderConfig('wechat'));

        expect(false !== strpos($result['ciphertext'], '-----BEGIN CERTIFICATE-----'))->toBeTrue();
    });

    it('解密资源（AES-256-GCM）', function () {
        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificate',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];
        $result = WechatTraitStub::decryptWechatResourceAes256Gcm(base64_decode($resource['ciphertext']), '53D67FCB97E68F9998CBD17ED7A8D1E2', $resource['nonce'], $resource['associated_data']);
        expect(false !== strpos($result, '-----BEGIN CERTIFICATE-----'))->toBeTrue();

        $resource = [
            'algorithm' => 'AEAD_AES_256_GCM',
            'associated_data' => 'certificatea',
            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
            'nonce' => '4196a5b75276',
        ];
        WechatTraitStub::decryptWechatResourceAes256Gcm(base64_decode($resource['ciphertext']), 'foo', $resource['nonce'], $resource['associated_data']);
    })->throws(DecryptException::class, null, Exception::DECRYPT_WECHAT_ENCRYPTED_DATA_INVALID);
});

describe('platformCert', function () {
    it('重新加载', function () {
        $config = WechatTraitStub::getProviderConfig('wechat', []);

        expect($config)->toBeInstanceOf(WechatConfig::class);
        $existingCert = CertManager::wechatGetCertBySerial($config->getTenant(), 'yansongda');

        $response = new Response(
            200,
            [],
            json_encode([
                'data' => [
                    [
                        'effective_time' => '2021-07-16T17:51:10+08:00',
                        'encrypt_certificate' => [
                            'algorithm' => 'AEAD_AES_256_GCM',
                            'associated_data' => 'certificate',
                            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                            'nonce' => '4196a5b75276',
                        ],
                        'expire_time' => '2026-07-15T17:51:10+08:00',
                        'serial_no' => 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57',
                    ],
                ],
            ])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $result = WechatTraitStub::reloadWechatPublicCerts([], 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57');

        expect(str_contains($result, '-----BEGIN CERTIFICATE-----'))->toBeTrue();

        $wechatConfig = Artful::get(ConfigInterface::class)->get('wechat.default');

        expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);
        expect(CertManager::wechatGetCertBySerial($wechatConfig->getTenant(), 'yansongda'))->toBe($existingCert);
        expect(CertManager::wechatGetCertBySerial('default', 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57'))->not->toBeNull();
    });

    it('获取平台证书', function () {
        $response = new Response(
            200,
            [],
            json_encode([
                'data' => [
                    [
                        'effective_time' => '2021-07-16T17:51:10+08:00',
                        'encrypt_certificate' => [
                            'algorithm' => 'AEAD_AES_256_GCM',
                            'associated_data' => 'certificate',
                            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                            'nonce' => '4196a5b75276',
                        ],
                        'expire_time' => '2026-07-15T17:51:10+08:00',
                        'serial_no' => 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57',
                    ],
                ],
            ])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $path = sys_get_temp_dir();

        WechatTraitStub::getWechatPublicCerts([], $path);

        $crtPathName = $path.'/test-45F59D4DABF31918AFCEC556D5D2C6E376675D57.crt';

        expect($crtPathName)->toBeFile();
        expect(str_contains(file_get_contents($crtPathName), '-----BEGIN CERTIFICATE-----'))->toBeTrue();

        $this->expectOutputRegex('/.*-----BEGIN CERTIFICATE-----/');
        WechatTraitStub::getWechatPublicCerts();
    });

    it('类型化配置获取', function () {
        $config = WechatTraitStub::getProviderConfig('wechat');

        expect($config)->toBeInstanceOf(WechatConfig::class);

        expect(realpath(CertManager::wechatGetCertBySerial($config->getTenant(), '45F59D4DABF31918AFCEC556D5D2C6E376675D57') ?? ''))->toEqual(realpath(cert_path('wechatAppPublicKey.pem')));
    });
});

describe('certSerial', function () {
    it('获取证书序列号', function () {
        // 不传证书
        $params = [];
        $result = WechatTraitStub::getWechatSerialNo($params);
        expect(in_array($result, ['45F59D4DABF31918AFCEC556D5D2C6E376675D57', 'yansongda']))->toBeTrue();

        // 传证书
        $params = ['_serial_no' => 'yansongda'];
        $result = WechatTraitStub::getWechatSerialNo($params);
        expect($result)->toEqual('yansongda');
    });

    it('从微信请求获取', function () {
        $response = new Response(
            200,
            [],
            json_encode([
                'data' => [
                    [
                        'effective_time' => '2021-07-16T17:51:10+08:00',
                        'encrypt_certificate' => [
                            'algorithm' => 'AEAD_AES_256_GCM',
                            'associated_data' => 'certificate',
                            'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                            'nonce' => '4196a5b75276',
                        ],
                        'expire_time' => '2026-07-15T17:51:10+08:00',
                        'serial_no' => 'test-45F59D4DABF31918AFCEC556D5D2C6E376675D57',
                    ],
                ],
            ])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $params = ['_config' => 'empty_wechat_public_cert'];
        $result = WechatTraitStub::getWechatSerialNo($params);
        expect($result)->toEqual('test-45F59D4DABF31918AFCEC556D5D2C6E376675D57');
    });
});

describe('virtualPay', function () {
    it('签名', function () {
        $config = new WechatConfig([
            'app_id' => 'yansongda',
            'mp_app_id' => 'wx55955316af4ef13',
            'mch_id' => '1600314069',
            'mini_app_id' => 'wx55955316af4ef14',
            'virtual_pay' => [
                'app_key' => '12345',
            ],
            'mch_secret_key_v2' => 'yansongda',
            'mch_secret_key' => '53D67FCB97E68F9998CBD17ED7A8D1E2',
            'mch_secret_cert' => cert_path('wechatAppPrivateKey.pem'),
            'mch_public_cert_path' => cert_path('wechatAppPublicKey.pem'),
            'notify_url' => 'https://pay.yansongda.cn',
            'wechat_public_cert_path' => [
                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => cert_path('wechatAppPublicKey.pem'),
            ],
            'mode' => PayMode::NORMAL,
        ]);

        $uri = '/xpay/query_user_balance';
        $body = '{"openid": "xxx", "user_ip": "127.0.0.1", "env": 0}';

        expect(WechatTraitStub::getWechatVirtualPaySignature($config, $uri, $body))->toEqual('c37809f27c6d7fd1837ad2500a04512b66b34fd793a39a385fade56dca89a4b5');
    });

    it('会话签名', function () {
        $body = '{"openid": "xxx", "user_ip": "127.0.0.1", "env": 0}';
        $sessionKey = '9hAb/NEYUlkaMBEsmFgzig==';

        expect(WechatTraitStub::getWechatVirtualSessionSignature($sessionKey, $body))->toEqual('089d9e8dc5d308977360c4b79ec600a93d736802802a807d634192328032f6c7');
    });

    it('获取微信公钥', function () {
        $serialNo = '45F59D4DABF31918AFCEC556D5D2C6E376675D57';
        $result = WechatTraitStub::getWechatPublicKey(WechatTraitStub::getProviderConfig('wechat'), $serialNo);
        expect($result)->toBeString();

        $serialNo = 'non-exist';
        WechatTraitStub::getWechatPublicKey(WechatTraitStub::getProviderConfig('wechat'), $serialNo);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_WECHAT_SERIAL_NOT_FOUND);

    it('access token（缓存）', function () {
        $config = WechatTraitStub::getProviderConfig('wechat');
        expect($config)->toBeInstanceOf(WechatConfig::class);

        $config->getVirtualPay()->setAccessToken('cached_token_123');
        $config->getVirtualPay()->setAccessTokenExpiry(time() + 3600);

        $token = WechatTraitStub::getWechatVirtualAccessToken([]);

        expect($token)->toEqual('cached_token_123');
    });

    it('access token 缺少配置', function () {

        WechatTraitStub::getWechatVirtualAccessToken([]);
    })->throws(InvalidConfigException::class, null, Exception::CONFIG_WECHAT_INVALID);

    it('access token（新取）', function () {
        $config = WechatTraitStub::getProviderConfig('wechat');
        expect($config)->toBeInstanceOf(WechatConfig::class);

        $config->getVirtualPay()->setAppSecret('test_app_secret');

        Pay::config([
            'wechat' => [
                'default' => $config->toArray(),
            ],
            '_force' => true,
        ]);

        $response = new Response(
            200,
            [],
            json_encode([
                'access_token' => 'new_wechat_token_456',
                'expires_in' => 7200,
            ])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $token = WechatTraitStub::getWechatVirtualAccessToken([]);

        expect($token)->toEqual('new_wechat_token_456');

        $wechatConfig = Pay::get(ConfigInterface::class)->get('wechat.default');

        expect($wechatConfig)->toBeInstanceOf(WechatConfig::class);
        expect($wechatConfig->getVirtualPay()->getAccessToken())->toEqual('new_wechat_token_456');
        expect($wechatConfig->getVirtualPay()->getAccessTokenExpiry())->not->toBeEmpty();
    });
});