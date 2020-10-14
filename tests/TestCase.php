<?php

namespace Yansongda\Pay\Tests;

use Yansongda\Pay\Pay;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        Pay::clear();
    }
}
