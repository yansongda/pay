<?php

namespace Yansongda\Pay\Tests\Plugin;

use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\Stubs\FooPackerStub;
use Yansongda\Pay\Tests\Stubs\FooParserStub;
use Yansongda\Pay\Tests\TestCase;

class ParserPluginTest extends TestCase
{
    protected ParserPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ParserPlugin();
    }

    public function testWrongParser()
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(InvalidConfigException::INVALID_PARSER);

        $rocket = new Rocket();
        $rocket->setDirection(FooParserStub::class);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testWrongPacker()
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(InvalidConfigException::INVALID_PACKER);

        $rocket = new Rocket();
        $rocket->setPacker(FooPackerStub::class);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testDefaultParser()
    {
        Pay::set(DirectionInterface::class, NoHttpRequestDirection::class);

        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }

    public function testObjectParser()
    {
        Pay::set(DirectionInterface::class, new NoHttpRequestDirection());

        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }
}
