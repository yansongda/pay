<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Apple;

use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Apple\Pay\PayTokenPlugin;
use Yansongda\Pay\Shortcut\Apple\PayTokenShortcut;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayTokenShortcutTest extends TestCase
{
    protected PayTokenShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new PayTokenShortcut();
    }

    public function testGetPlugins()
    {
        self::assertEquals([
            StartPlugin::class,
            PayTokenPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }

    public function testPayToken()
    {
        $token = AppleTokenFactory::makeToken(['applicationPrimaryAccountID' => '123456', 'totalAmount' => '100']);

        $result = Pay::apple()->payToken(['token' => $token]);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('123456', $result->get('applicationPrimaryAccountID'));
        self::assertEquals('100', $result->get('totalAmount'));
        self::assertEquals($token['header'], $result->get('_token_header'));
    }
}
