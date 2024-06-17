<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Epay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Epay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Epay\ResponsePlugin;
use Yansongda\Pay\Plugin\Epay\StartPlugin;
use Yansongda\Pay\Plugin\Epay\VerifySignaturePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class EpayTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::epay()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::wechat()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignPlugin::class, AddRadarPlugin::class, VerifySignaturePlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::epay()->mergeCommonPlugins($plugins));
    }
    public function testSuccess()
    {
        $result = Pay::epay()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
