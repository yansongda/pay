<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

class AlipayTraitStub
{
    use AlipayTrait;
}

class AlipayTraitTest extends TestCase
{
    public function testVerifyAlipaySignSuccess(): void
    {
        AlipayTraitStub::getProviderConfig('alipay');

        AlipayTraitStub::verifyAlipaySign(
            AlipayTraitStub::getProviderConfig('alipay'),
            json_encode([
                'code' => '10000',
                'msg' => 'Success',
                'order_id' => '20231220110070000002150000657610',
                'out_biz_no' => '2023122022560000',
                'pay_date' => '2023-12-20 22:56:33',
                'pay_fund_order_id' => '20231220110070001502150000660902',
                'status' => 'SUCCESS',
                'trans_amount' => '0.01',
            ], JSON_UNESCAPED_UNICODE),
            'eITxP5fZiJPB2+vZb90IRkv2iARxeNx/6Omxk7FStqflhG5lMoCvGjo2FZ6Szo1bGBMBReazZuqLaqsgomWAUO9onMVurB3enLbRvwUlpE7XEZaxk/sJYjgc2Y7pIAenvnLL9PEAiXmvUvuinUlvS9J2r1XysC0p/2wu7kEJ/GgZpFDIIYY9mdM6U1rGbi+RvirQXtQHmaEuuJWLA75NR1bvfG3L8znzW9xz1kOQqOWsQmD/bF1CDWbozNLwLCUmClRJz0Fj4mUYRF0zbW2VP8ZgHu1YvVKJ2+dWC9b+0o94URk7psIpc5NjiOM9Jsn6aoC2CfrJ/sqFMRCkYWzw6A=='
        );
        self::assertTrue(true);
    }

    public function testVerifyAlipaySignConfigError(): void
    {
        $config1 = [
            'alipay' => [
                'default' => [
                    'alipay_public_cert_path' => '',
                ],
            ],
        ];

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

        Pay::config(array_merge($config1, ['_force' => true]));
    }

    public function testVerifyAlipaySignEmpty(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);
        AlipayTraitStub::verifyAlipaySign(AlipayTraitStub::getProviderConfig('alipay'), '', '');
    }

    public function testGetAlipayUrlDefault(): void
    {
        self::assertSame(
            Alipay::URL[Pay::MODE_NORMAL],
            AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(), null)
        );
    }

    public function testGetAlipayUrlSandbox(): void
    {
        self::assertSame(
            Alipay::URL[Pay::MODE_SANDBOX],
            AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(Pay::MODE_SANDBOX), null)
        );
    }

    public function testGetAlipayUrlWithPayload(): void
    {
        self::assertSame(
            'https://example.com/alipay',
            AlipayTraitStub::getAlipayUrl($this->getAlipayConfig(), new Collection(['_url' => 'https://example.com/alipay']))
        );
    }

    protected function getAlipayConfig(int $mode = Pay::MODE_NORMAL): AlipayConfig
    {
        return new AlipayConfig([
            'app_id' => 'app_id',
            'app_secret_cert' => 'app_secret_cert',
            'app_public_cert_path' => 'app_public_cert_path',
            'alipay_public_cert_path' => 'alipay_public_cert_path',
            'alipay_root_cert_path' => 'alipay_root_cert_path',
            'mode' => $mode,
        ], 'default');
    }
}
