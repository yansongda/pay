<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\ArrayParser;
use Yansongda\Pay\Tests\TestCase;

class ArrayParserTest extends TestCase
{
    public function testResponseNull()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(InvalidResponseException::RESPONSE_NONE);

        $parser = new ArrayParser();
        $parser->parse(null);
    }

    public function testWrongFormat()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(InvalidResponseException::UNPACK_RESPONSE_ERROR);

        $response = new Response(200, [], '{"name": "yansongda"}a');

        $parser = new ArrayParser();
        $parser->parse($response);
    }

    public function testNormal()
    {
        $response = new Response(200, [], '{"name": "yansongda"}');

        $parser = new ArrayParser();
        $result = $parser->parse($response);

        self::assertEquals(['name' => 'yansongda'], $result);
    }
}
