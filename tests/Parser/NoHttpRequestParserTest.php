<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Parser\NoHttpRequestParser;

class NoHttpRequestParserTest extends TestCase
{
    public function testNormal()
    {
        $response = new Response(200, [], '{"name": "yansongda"}');

        $parser = new NoHttpRequestParser();
        $result = $parser->parse($response);

        self::assertSame($response, $result);
    }

    public function testNull()
    {
        $parser = new NoHttpRequestParser();
        $result = $parser->parse(null);

        self::assertNull($result);
    }
}
