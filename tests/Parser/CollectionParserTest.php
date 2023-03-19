<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class CollectionParserTest extends TestCase
{
    protected CollectionParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new CollectionParser();
    }

    public function testNormal()
    {
        Pay::config();

        $response = new Response(200, [], '{"name": "yansongda"}');

        $result = $this->parser->parse(new JsonPacker(), $response);

        self::assertEquals(['name' => 'yansongda'], $result->all());
    }
}
