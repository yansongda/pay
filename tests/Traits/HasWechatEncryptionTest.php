<?php

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
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
