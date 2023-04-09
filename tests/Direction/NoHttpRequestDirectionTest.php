<?php

namespace Yansongda\Pay\Tests\Direction;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Tests\TestCase;

class NoHttpRequestDirectionTest extends TestCase
{
    protected NoHttpRequestDirection $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new NoHttpRequestDirection();
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
