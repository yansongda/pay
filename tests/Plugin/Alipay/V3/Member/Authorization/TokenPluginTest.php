<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\Authorization;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization\TokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class TokenPluginTest extends TestCase
{
    protected TokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TokenPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'grant_type' => 'authorization_code',
            'code' => 'auth_code',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/system/oauth/token', $result->getPayload()->get('_url'));
        self::assertSame('authorization_code', $result->getPayload()->get('_body')['grant_type']);
        self::assertSame('auth_code', $result->getPayload()->get('_body')['code']);
    }
}
