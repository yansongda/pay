<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\Authorization;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'user_id' => '2088000000000000',
        ]), fn ($rocket) => $rocket);

        self::assertSame('GET', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/open/auth/userauth/relationship/query?user_id=2088000000000000', $result->getPayload()->get('_url'));
        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
