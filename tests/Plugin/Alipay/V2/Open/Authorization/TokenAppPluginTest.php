<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Open\Authorization;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class TokenAppPluginTest extends TestCase
{
    protected TokenAppPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TokenAppPlugin();
    }

    public function testNormal()
    {
        $params = ['grant_type' => 'authorization_code', 'code' => '123456'];

        $rocket = (new Rocket())
            ->setParams($params)
            ->setPayload(new Collection(['app_auth_token' => 'xxx']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertEquals('alipay.open.auth.token.app', $payload->get('method'));
        self::assertEquals($params, $payload->get('biz_content'));
        self::assertFalse($payload->has('app_auth_token'));
    }
}
