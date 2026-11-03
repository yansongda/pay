<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\AddPayloadSignaturePlugin as VirtualAddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\CallbackPlugin as VirtualCallbackPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CurrencyPayPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\QueryBalancePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\RefundOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin as VirtualCheckResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\CallbackPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\CallbackPluginStub;
use Yansongda\Pay\Tests\Stubs\Plugin\VirtualCallbackPluginStub;
use Yansongda\Pay\Tests\Stubs\Plugin\VirtualCheckResponsePluginStub;
use Yansongda\Pay\Tests\Stubs\Plugin\VerifySignaturePluginStub;

describe('shortcut', function () {
    it('不存在', function () {

        Pay::wechat()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);

    it('不兼容', function () {

        Pay::wechat()->foo();
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_INVALID);
});

describe('cancel', function () {
    it('正常', function () {
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

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VerifySignaturePlugin::class, new VerifySignaturePluginStub());

        Pay::wechat()->cancel(['out_bill_no' => '123']);

        expect(true)->toBeTrue();
    });

    it('错误响应码', function () {
        $response = new Response(
            400,
            [],
            json_encode(['error' => 'error message']),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        Pay::wechat()->cancel(['out_bill_no' => '123']);
    })->throws(InvalidResponseException::class, null, \Yansongda\Pay\Exception\Exception::RESPONSE_CODE_WRONG);
});

describe('close', function () {
    it('正常', function () {
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

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VerifySignaturePlugin::class, new VerifySignaturePluginStub());

        Pay::wechat()->close(['out_trade_no' => '123']);

        expect(true)->toBeTrue();
    });

    it('错误响应码', function () {
        $response = new Response(
            400,
            [],
            json_encode(['error' => 'error message']),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);

        Pay::wechat()->close(['out_trade_no' => '123']);
    })->throws(InvalidResponseException::class, null, \Yansongda\Pay\Exception\Exception::RESPONSE_CODE_WRONG);
});

describe('callback', function () {
    it('正常', function () {
        $request = new ServerRequest(
            'POST',
            'https://pay.yansongda.cn/wechat/notify',
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'NmmOwkXg89J9UP9gnoGeGZUdSflYhOzD/Imxv0SZf09+42Yn+u8DHQs/QcsOtD1O9hi38PizLnyMQ7NyqkQqVZCu7ID532FOiKkU6qIhrrCrm8w5ktJTXUorH8gEQtxZSKJ0Z0I/fsOBhnvoBRRlIyvEwoESAcJuyJYCgQuvFaqGYDOjLW7umTdO0vUZnH9TJfxfziLxwEYoH09D43H+hXL4oKAF+aIdAiWyS/CwnE4BB8j4NGsCi4v4cAZkJjqV45koAtzXVBzYjccURNSRUYOZv9IW7CqOFmOWsmAN5bVncs4S89lMNW+UIqNRx4tpIrU4CuX81V0tVFcweKnqNQ==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode([
                'resource' => [
                    'algorithm' => 'AEAD_AES_256_GCM',
                    'associated_data' => 'certificate',
                    'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                    'nonce' => '4196a5b75276',
                ],
            ]),
        );

        Pay::set(CallbackPlugin::class, new CallbackPluginStub());

        $result = Pay::wechat()->callback($request);

        expect($result->get('resource.ciphertext'))->not->toBeEmpty();
    });

    it('虚拟支付', function () {
        $body = '<xml><Encrypt><![CDATA[test-encrypt-data]]></Encrypt></xml>';

        $request = new ServerRequest(
            'POST',
            'https://pay.yansongda.cn/wechat/virtual/notify',
            ['Content-Type' => 'application/xml'],
            $body,
        );

        $request = $request->withQueryParams([
            'msg_signature' => 'test-signature',
            'timestamp' => '1626444144',
            'nonce' => 'test-nonce',
        ]);

        Pay::set(VirtualCallbackPlugin::class, new VirtualCallbackPluginStub());

        $result = Pay::wechat()->callback($request, ['_action' => 'virtual']);

        expect($result)->not->toBeEmpty();
    });

    it('默认不变', function () {
        $request = new ServerRequest(
            'POST',
            'https://pay.yansongda.cn/wechat/notify',
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'NmmOwkXg89J9UP9gnoGeGZUdSflYhOzD/Imxv0SZf09+42Yn+u8DHQs/QcsOtD1O9hi38PizLnyMQ7NyqkQqVZCu7ID532FOiKkU6qIhrrCrm8w5ktJTXUorH8gEQtxZSKJ0Z0I/fsOBhnvoBRRlIyvEwoESAcJuyJYCgQuvFaqGYDOjLW7umTdO0vUZnH9TJfxfziLxwEYoH09D43H+hXL4oKAF+aIdAiWyS/CwnE4BB8j4NGsCi4v4cAZkJjqV45koAtzXVBzYjccURNSRUYOZv9IW7CqOFmOWsmAN5bVncs4S89lMNW+UIqNRx4tpIrU4CuX81V0tVFcweKnqNQ==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode([
                'resource' => [
                    'algorithm' => 'AEAD_AES_256_GCM',
                    'associated_data' => 'certificate',
                    'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                    'nonce' => '4196a5b75276',
                ],
            ]),
        );

        Pay::set(CallbackPlugin::class, new CallbackPluginStub());

        $result = Pay::wechat()->callback($request);

        expect($result->get('resource.ciphertext'))->not->toBeEmpty();
    });

    it('空参数', function () {
        $request = new ServerRequest(
            'POST',
            'https://pay.yansongda.cn/wechat/notify',
            [
                'Wechatpay-Nonce' => 'e59e78a6c3f7dfd7e84aabee71be0452',
                'Wechatpay-Signature' => 'NmmOwkXg89J9UP9gnoGeGZUdSflYhOzD/Imxv0SZf09+42Yn+u8DHQs/QcsOtD1O9hi38PizLnyMQ7NyqkQqVZCu7ID532FOiKkU6qIhrrCrm8w5ktJTXUorH8gEQtxZSKJ0Z0I/fsOBhnvoBRRlIyvEwoESAcJuyJYCgQuvFaqGYDOjLW7umTdO0vUZnH9TJfxfziLxwEYoH09D43H+hXL4oKAF+aIdAiWyS/CwnE4BB8j4NGsCi4v4cAZkJjqV45koAtzXVBzYjccURNSRUYOZv9IW7CqOFmOWsmAN5bVncs4S89lMNW+UIqNRx4tpIrU4CuX81V0tVFcweKnqNQ==',
                'Wechatpay-Timestamp' => '1626444144',
                'Wechatpay-Serial' => '45F59D4DABF31918AFCEC556D5D2C6E376675D57',
            ],
            json_encode([
                'resource' => [
                    'algorithm' => 'AEAD_AES_256_GCM',
                    'associated_data' => 'certificate',
                    'ciphertext' => 'kbbHAUhBwdjYZkHPW149MW/8WNpxQo1Gyp4kVNVjd+zrXnyOFhgZic2U2+tobFAgfdr93zr0JZF3FdbxgkaOAV2NAeCfU8jsUYXSfn7fM8487jXMVXKKEneGiiv1/bDLkz7KFsTfu2y5Rv+igWQ+bvCUQAwoNzjupTXnnDR5hBiofZcFLHL45govyYE2o0qD5SLiJHcFS4pg/IOx8SIqUFNepr3piKXUxKowU8/kNxXyRzL8yp7XnhrzAzclupvjveNwZyiw3TqlLZdR5TbEFLCogWaRHZRqz3vKEfgRaUYUtXCtQVrm+adbSDBFIq34v+XfeIHMz9pKhH/m80N5Hx69hPzbvIdBhzwaEDyN3h8gaeYKFyW9xIAs5jCrzzUEkKyMzOKzx7XA+1HRakSyvs6RlkRTa/ztBy6aZL0nxK6XMZ9tA7zdf2VnBX/7WPQYRzoky0cVyH1KRZxI7In2hfvpjSvl6P7Adzp+EZXYM/dINTrrg+RQRe60tPy7vgE8PZZf+SAWzSZPWIm7Lx6GksJX0vnT4gOeTAPw6EeFsYU/ZD7fYslJOEbA14yHBrJFkwDpSI8aSHp2nZYbruM0y8IKr0p3vjN80Ko3jiRPxj4uNdJliR9WDCV22b9JeadAaJhO9+oSNbbtFnFTCZjXbf8rMz5KCGVrGRvUyB70zhRxYIOdTYKAEkmbU7jcMLd0aufuQqIw0WviQHB+ztrkjBCFwPu5/hlRVj9opNFnzYNltfVGrA1XW3NQ4FaMNah95ahomAG/+S7zJqq4Gvk1O/PgQ9kMP0adY3GlrHUNqr2zC709IervMQ1pEdcuNEln3V5TSDiE0x7BjoMoN2m+MKAIhw59VxzHGNmJELbkKsZUhKKXFFyEXFsw143/9IYOyanmHQxujdIBKI0rxYkVz9QgaajisCzdnRf0ymnkceGGnYsP7VTYBnuCncjgHxbEn3emlTRygEjgj/epupsQL2tfW+snxnafEM+Pc079pUYmKeCUEUoX/FUmdFIf8hlSHBTjEVMGsNUI/u2W781RBDfk2X/2QQQm3NOjgZ3le6hxEQqc12yANTvdq7cFVllWqMHBsXPCjpHWIHcS5BMkImoD7s6WItq60yJA8ioGJf3Rba+Yb/YeBBNxjDnXtAmX/2hJIsxEFLTYGUvdmFC5jeb5ifrOuxnLciKM8y4nLZ28dDsvVsaBBAMAFYfWb5NymKUDhhngR5bDuW4sKccZ6DmYQeStHT1fn2yoSneGA70HctQSWZ2roTdNihPTCs7rYD0dFeQ+SfLOJzMN4c5GbJ6n5tdCjERcLGIaXEKacfySo7e4VZtHeHowvlvBclS9pooZqzHd+EFlJEYywEs9jURgsJY2yHJt2zTZeIdsvM8KK5v0NkH8FiPbWqFG8LaRmUrqhJGLuLLRTcJnt6YVYESxUVTb3pmriUbXfg/ThHF/y0THyrM6bVDNOwNWZOpMYPPNaVmOTX39JdYayWl2HX0n8AsIRmevXzD4N9iDh2HGwie4gh92Qdcogwua++uhkhSsLFuWBpJiaPdxVtzz3E3jHfy+yryfh6msaXc/jmhwqBm/ii3j76lDP5YaRv4+JWZmom72+pmZuKD8qPKrPRxI2/aGiKEqgs25knpLLnbAhWAEYeIzVK1sQkjc5JFss1Std8FdDrHeM6agAB+MWncK1LloXZmiwz/6WmlwSDepnGHqLEciXThAZq6FwunJZTcHY9LamJgIY81c9t/KHlSFqlc/9mW4OZHM4BOZQ5sTj5PWE+OP2Aq9CKdJqoK3OmphBg2ewjrZt5/tSn9jpk6NlVrHD7MsJcKi5a0he4qvNPh1cHqUqWcF4rBFmfPptdHIBV77LXnizJZMUAwf16KsmJpwJg==',
                    'nonce' => '4196a5b75276',
                ],
            ]),
        );

        Pay::set(CallbackPlugin::class, new CallbackPluginStub());

        $result = Pay::wechat()->callback($request, null);

        expect($result->get('resource.ciphertext'))->not->toBeEmpty();
    });
});

describe('success', function () {
    it('正常', function () {
        $result = Pay::wechat()->success();

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect((string) $result->getBody())->toContain('SUCCESS');
    });

    it('默认不变', function () {
        $result = Pay::wechat()->success();

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect($result->getStatusCode())->toEqual(200);
        expect(json_decode((string) $result->getBody(), true))->toEqual(['code' => 'SUCCESS', 'message' => '成功']);
    });
});

describe('payScore', function () {
    it('查询', function () {
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

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VerifySignaturePlugin::class, new VerifySignaturePluginStub());

        // QueryPlugin 会 setPayload 覆盖为完整 GET URL；此处仅需合法 out_order_no 跑通全管道
        // mock 响应沿用 testCancel 的签名头与配套 body，保证真实 openssl 验签通过（Stub 仅绕过时间戳校验）
        Pay::wechat()->payscore(['_action' => 'query', 'out_order_no' => '123']);

        expect(true)->toBeTrue();
    });

    it('成功响应', function () {
        $result = Pay::wechat()->success(['_action' => 'payscore']);

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect($result->getStatusCode())->toEqual(204);
        expect((string) $result->getBody())->toEqual('');
        expect($result->getHeaderLine('Content-Type'))->toContain('application/json');
    });
});

describe('virtualPay', function () {
    it('成功', function () {
        $result = Pay::wechat()->success(['_action' => 'virtual']);

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect($result->getStatusCode())->toEqual(200);
        expect($result->getHeaderLine('Content-Type'))->toContain('application/xml');

        $body = (string) $result->getBody();
        expect($body)->toContain('<ErrCode>0</ErrCode>');
        expect($body)->toContain('<ErrMsg>success</ErrMsg>');
    });

    it('成功（JSON）', function () {
        $result = Pay::wechat()->success(['_action' => 'virtual', '_format' => 'json']);

        expect($result)->toBeInstanceOf(ResponseInterface::class);
        expect($result->getStatusCode())->toEqual(200);
        expect($result->getHeaderLine('Content-Type'))->toContain('application/json');

        $body = json_decode((string) $result->getBody(), true);
        expect($body['ErrCode'])->toEqual(0);
        expect($body['ErrMsg'])->toEqual('success');
    });

    it('余额查询', function () {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['errcode' => 0, 'errmsg' => 'OK', 'balance' => 1000], JSON_UNESCAPED_SLASHES),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VirtualCheckResponsePlugin::class, new VirtualCheckResponsePluginStub());

        $result = Pay::wechat()->pay(
            [
                StartPlugin::class,
                QueryBalancePlugin::class,
                AddPayloadBodyPlugin::class,
                VirtualAddPayloadSignaturePlugin::class,
                AddRadarPlugin::class,
                VirtualCheckResponsePlugin::class,
                ParserPlugin::class,
            ],
            [
                'openid' => 'test-openid',
                'user_ip' => '127.0.0.1',
                'env' => 0,
                'access_token' => 'test-access-token',
            ],
        );

        expect($result)->not->toBeEmpty();
    });

    it('虚拟币支付', function () {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['errcode' => 0, 'errmsg' => 'OK'], JSON_UNESCAPED_SLASHES),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VirtualCheckResponsePlugin::class, new VirtualCheckResponsePluginStub());

        $result = Pay::wechat()->pay(
            [
                StartPlugin::class,
                CurrencyPayPlugin::class,
                AddPayloadBodyPlugin::class,
                VirtualAddPayloadSignaturePlugin::class,
                AddRadarPlugin::class,
                VirtualCheckResponsePlugin::class,
                ParserPlugin::class,
            ],
            [
                'openid' => 'test-openid',
                'user_ip' => '127.0.0.1',
                'env' => 0,
                'access_token' => 'test-access-token',
                'amount' => 100,
                'order_id' => 'test-order-id',
                'payitem' => 'test-payitem',
                'remark' => 'test-remark',
            ],
        );

        expect($result)->not->toBeEmpty();
    });

    it('查单', function () {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['errcode' => 0, 'errmsg' => 'OK', 'order_id' => 'test-order-id'], JSON_UNESCAPED_SLASHES),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VirtualCheckResponsePlugin::class, new VirtualCheckResponsePluginStub());

        $result = Pay::wechat()->pay(
            [
                StartPlugin::class,
                QueryOrderPlugin::class,
                AddPayloadBodyPlugin::class,
                VirtualAddPayloadSignaturePlugin::class,
                AddRadarPlugin::class,
                VirtualCheckResponsePlugin::class,
                ParserPlugin::class,
            ],
            [
                'openid' => 'test-openid',
                'env' => 0,
                'access_token' => 'test-access-token',
                'order_id' => 'test-order-id',
                'out_trade_no' => 'test-out-trade-no',
            ],
        );

        expect($result)->not->toBeEmpty();
    });

    it('退款', function () {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['errcode' => 0, 'errmsg' => 'OK'], JSON_UNESCAPED_SLASHES),
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);
        Pay::set(HttpClientInterface::class, $http);
        Pay::set(VirtualCheckResponsePlugin::class, new VirtualCheckResponsePluginStub());

        $result = Pay::wechat()->pay(
            [
                StartPlugin::class,
                RefundOrderPlugin::class,
                AddPayloadBodyPlugin::class,
                VirtualAddPayloadSignaturePlugin::class,
                AddRadarPlugin::class,
                VirtualCheckResponsePlugin::class,
                ParserPlugin::class,
            ],
            [
                'openid' => 'test-openid',
                'env' => 0,
                'access_token' => 'test-access-token',
                'order_id' => 'test-order-id',
                'refund_order_id' => 'test-refund-order-id',
                'left_fee' => 100,
                'refund_fee' => 50,
                'biz_meta' => 'test-meta',
                'refund_reason' => '1',
                'req_from' => '2',
            ],
        );

        expect($result)->not->toBeEmpty();
    });

    it('回调动作路由', function () {
        $body = '<xml><Encrypt><![CDATA[test-encrypt-data]]></Encrypt></xml>';

        $request = new ServerRequest(
            'POST',
            'https://pay.yansongda.cn/wechat/virtual/notify',
            ['Content-Type' => 'application/xml'],
            $body,
        );

        $request = $request->withQueryParams([
            'msg_signature' => 'test-signature',
            'timestamp' => '1626444144',
            'nonce' => 'test-nonce',
        ]);

        Pay::set(VirtualCallbackPlugin::class, new VirtualCallbackPluginStub());

        $result = Pay::wechat()->callback($request, ['_action' => 'virtual']);

        expect($result)->not->toBeEmpty();
        expect($result->get('EventType'))->toEqual('OpenProductBuy');
        expect($result->get('OutTradeNo'))->toEqual('test-order-no');
    });
});