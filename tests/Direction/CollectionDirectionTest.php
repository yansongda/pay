<?php

namespace Yansongda\Pay\Tests\Direction;

use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Direction\CollectionDirection;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Packer\QueryPacker;
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
        $response = new Response(200, [], '{"name": "yansongda"}');

        $result = $this->parser->guide(new JsonPacker(), $response);

        self::assertEquals(['name' => 'yansongda'], $result->all());
    }

    public function testResponseNull()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_EMPTY);

        $this->parser->guide(new JsonPacker(), null);
    }

    public function testWrongFormat()
    {
        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_UNPACK_ERROR);

        $response = new Response(200, [], '{"name": "yansongda"}a');

        $this->parser->guide(new JsonPacker(), $response);
    }

    public function testReadContents()
    {
        $response = new Response(200, [], '{"name": "yansongda"}');

        $response->getBody()->read(2);

        $result = $this->parser->guide(new JsonPacker(), $response);

        self::assertEquals(['name' => 'yansongda'], $result->toArray());
    }

    public function testQueryBody()
    {
        $response = new Response(200, [], 'name=yansongda&age=29');

        $result = $this->parser->guide(new QueryPacker(), $response);

        self::assertEqualsCanonicalizing(['name' => 'yansongda', 'age' => '29'], $result->toArray());
    }

    public function testJsonWith()
    {
        $url = 'https://yansongda.cn?name=yansongda&age=29';

        $response = new Response(200, [], json_encode(['h5_url' => $url]));

        $result = $this->parser->guide(new JsonPacker(), $response);

        self::assertEquals('https://yansongda.cn?name=yansongda&age=29', $result['h5_url']);
    }
}
