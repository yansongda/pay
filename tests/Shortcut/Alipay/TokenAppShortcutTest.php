<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\TokenAppShortcut;
use Yansongda\Pay\Tests\TestCase;

class TokenAppShortcutTest extends TestCase
{
    protected TokenAppShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new TokenAppShortcut();
    }

    public function testFooParam()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->shortcut->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            TokenAppPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testQuery()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'query']);

        self::assertEquals([
            StartPlugin::class,
            TokenAppQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
