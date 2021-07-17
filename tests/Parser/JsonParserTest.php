<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Parser\JsonParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class JsonParserTest extends TestCase
{
    public function testNormal()
    {
        Pay::config([]);

        $response = new Response(200, [], '{"name": "yansongda"}');

        $parser = new JsonParser();
        $result = $parser->parse($response);

        self::assertEquals(json_encode(['name' => 'yansongda']), $result);
    }
}
