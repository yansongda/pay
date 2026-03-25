<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\DetailPlugin;
use Yansongda\Pay\Tests\TestCase;

class DetailPluginTest extends TestCase
{
    protected DetailPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DetailPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'auth_token' => 'test_auth_token',
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/user/info/share?auth_token=test_auth_token', $result->getPayload()->get('_url'));
        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
