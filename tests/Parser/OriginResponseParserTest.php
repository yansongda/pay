<?php

namespace Yansongda\Pay\Tests\Parser;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\OriginResponseParser;
use Yansongda\Pay\Tests\TestCase;

class OriginResponseParserTest extends TestCase
{
    public function testResponseNull()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::INVALID_RESPONSE_CODE);

        $parser = new OriginResponseParser();
        $parser->parse(null);
    }
}
