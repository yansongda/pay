<?php

namespace Yansongda\Pay\Tests\Direction;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Direction\CollectionDirection;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class CollectionDirectionTest extends TestCase
{
    protected CollectionDirection $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new CollectionDirection();
    }

    public function testNormal()
    {
        Pay::config();

        $response = new Response(200, [], '{"name": "yansongda"}');

        $result = $this->parser->parse(new JsonPacker(), $response);

        self::assertEquals(['name' => 'yansongda'], $result->all());
    }
}
