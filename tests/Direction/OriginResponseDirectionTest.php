<?php

namespace Yansongda\Pay\Tests\Direction;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Direction\OriginResponseDirection;
use Yansongda\Pay\Tests\TestCase;

class OriginResponseDirectionTest extends TestCase
{
    protected OriginResponseDirection $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new OriginResponseDirection();
    }

    public function testResponseNull()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_EMPTY);

        $this->parser->guide(new JsonPacker(), null);
    }
}
