<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Tests\TestCase;

class NoHttpRequestParserTest extends TestCase
{
    protected NoHttpRequestParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new NoHttpRequestParser();
    }

    public function testNormal()
    {
        $response = new Response(200, [], '{"name": "yansongda"}');

        $result = $this->parser->parse(new JsonPacker(), $response);

        self::assertSame($response, $result);
    }

    public function testNull()
    {
        $result = $this->parser->parse(new JsonPacker(), null);

        self::assertNull($result);
    }
}
