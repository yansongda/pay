<?php

namespace Yansongda\Pay\Tests\Parser;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Tests\TestCase;

class OriginResponseParserTest extends TestCase
{
    protected OriginResponseParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new OriginResponseParser();
    }

    public function testResponseNull()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::INVALID_RESPONSE_CODE);

        $this->parser->parse(new JsonPacker(), null);
    }
}
