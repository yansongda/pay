<?php

namespace Yansongda\Pay\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Pay;

class CollectionParserTest extends TestCase
{
    public function testNormal()
    {
        Pay::config([]);

        $response = new Response(200, [], '{"name": "yansongda"}');

        $parser = new CollectionParser();
        $result = $parser->parse($response);

        self::assertEquals(['name' => 'yansongda'], $result->all());
    }
}
