<?php

namespace Yansongda\Pay\Tests\Packer;

use Yansongda\Pay\Packer\QueryPacker;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPackerTest extends TestCase
{
    protected QueryPacker $packer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packer = new QueryPacker();
    }

    public function testPack()
    {
        $array = ['name' => 'yansongda', 'age' => '29'];
        $str = 'name=yansongda&age=29';

        self::assertEquals($str, $this->packer->pack($array));
        self::assertEquals($str, $this->packer->pack(Collection::wrap($array)));
    }

    public function testUnpack()
    {
        $array = ['name' => 'yansongda', 'age' => '29'];
        $str = 'name=yansongda&age=29';

        self::assertEquals($array, $this->packer->unpack($str));
    }

    public function testUnpackBlank()
    {
        $array = ['name' => 'yan+song+da', 'age' => '29'];
        $str = 'name=yan+song+da&age=29';

        self::assertEquals($array, $this->packer->unpack($str));
    }
}
