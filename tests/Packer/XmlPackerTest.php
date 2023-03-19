<?php

namespace Yansongda\Pay\Tests\Packer;

use Yansongda\Pay\Packer\XmlPacker;

class XmlPackerTest extends \Yansongda\Pay\Tests\TestCase
{
    protected XmlPacker $packer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packer = new XmlPacker();
    }

    public function testPack()
    {
        $xml = '<xml><name><![CDATA[yansongda]]></name><age>29</age></xml>';
        $array = ['name' => 'yansongda', 'age' => 29];

        self::assertEquals($xml, $this->packer->pack($array));
    }

    public function testUnpack()
    {
        $xml = '<xml><name><![CDATA[yansongda]]></name><age>29</age></xml>';
        $array = ['name' => 'yansongda', 'age' => 29];

        self::assertEquals($array, $this->packer->unpack($xml));
    }
}
