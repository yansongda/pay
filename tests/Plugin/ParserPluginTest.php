<?php

namespace Yansongda\Pay\Tests\Plugin;

use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\Stubs\FooPackerStub;
use Yansongda\Pay\Tests\TestCase;

class ParserPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\ParserPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ParserPlugin();
    }

    public function testPackerWrong()
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(InvalidConfigException::INVALID_PACKER);

        $rocket = new Rocket();
        $rocket->setDirection(FooPackerStub::class);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPackerDefault()
    {
        Pay::set(ParserInterface::class, NoHttpRequestParser::class);

        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }

    public function testPackerObject()
    {
        Pay::set(ParserInterface::class, new NoHttpRequestParser());

        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }
}
