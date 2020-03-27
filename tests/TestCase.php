<?php

namespace Yansongda\Pay\Tests;

use Yansongda\Pay\Pay;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        Pay::clear();
    }
}
