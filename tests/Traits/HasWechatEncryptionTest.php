<?php

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\Stubs\Traits\HasWechatEncryptionStub;
use Yansongda\Pay\Tests\TestCase;

class HasWechatEncryptionTest extends TestCase
{
    /**
     * @var HasWechatEncryptionStub
     */
    protected $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = new HasWechatEncryptionStub();
    }

    public function testLoadSerialNo()
    {
        // 不传证书
        $params = [];
        $result = $this->trait->loadSerialNo($params);
        self::assertTrue(in_array($result['_serial_no'], ['45F59D4DABF31918AFCEC556D5D2C6E376675D57', 'yansongda']));

        // 传证书
        $params = ['_serial_no' => 'yansongda',];
        $result = $this->trait->loadSerialNo($params);
        self::assertEquals('yansongda', $result['_serial_no']);
    }

    public function testLoadSerialNoWithRequestWechat()
    {
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
                    ]
                ]
            ])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $params = ['_config' => 'empty_wechat_public_cert'];
        $result = $this->trait->loadSerialNo($params);
        self::assertEquals('test-45F59D4DABF31918AFCEC556D5D2C6E376675D57', $result['_serial_no']);
    }

    public function testGetPublicKey()
    {
        $serialNo = '45F59D4DABF31918AFCEC556D5D2C6E376675D57';
        $result = $this->trait->getPublicKey([], $serialNo);
        self::assertIsString($result);

        $serialNo = 'non-exist';
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::WECHAT_SERIAL_NO_NOT_FOUND);
        $this->trait->getPublicKey([], $serialNo);
    }
}
