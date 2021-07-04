<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Parser\JsonParser;
use Yansongda\Pay\Pay;

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
