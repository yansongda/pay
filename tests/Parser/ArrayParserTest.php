<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Parser\ArrayParser;
use Yansongda\Pay\Tests\TestCase;

class ArrayParserTest extends TestCase
{
    public function testResponseNull()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_NONE);

        $parser = new ArrayParser();
        $parser->parse(null);
    }

    public function testWrongFormat()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::UNPACK_RESPONSE_ERROR);

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

    public function testReadContents()
    {
        $response = new Response(200, [], '{"name": "yansongda"}');

        $response->getBody()->read(2);

        $parser = new ArrayParser();
        $result = $parser->parse($response);

        self::assertEquals(['name' => 'yansongda'], $result);
    }

    public function testQueryBody()
    {
        $response = new Response(200, [], 'name=yansongda&age=29');

        $parser = new ArrayParser();
        $result = $parser->parse($response);

        self::assertEqualsCanonicalizing(['name' => 'yansongda', 'age' => '29'], $result);
    }

    public function testJsonWith()
    {
        $url = 'https://yansongda.cn?name=yansongda&age=29';

        $response = new Response(200, [], json_encode(['h5_url' => $url]));

        $parser = new ArrayParser();
        $result = $parser->parse($response);

        self::assertEquals('https://yansongda.cn?name=yansongda&age=29', $result['h5_url']);
    }
}
