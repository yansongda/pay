<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Open\Authorization;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppQueryPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class TokenAppQueryPluginTest extends TestCase
{
    protected TokenAppQueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TokenAppQueryPlugin();
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
        self::assertEquals('alipay.open.auth.token.app.query', $payload->get('method'));
        self::assertEquals($params, $payload->get('biz_content'));
        self::assertFalse($payload->has('app_auth_token'));
    }

    public function testNormalWithAction()
    {
        $params = ['grant_type' => 'authorization_code', 'code' => '123456', '_action' => 'query'];

        $rocket = (new Rocket())
            ->setParams($params)
            ->setPayload(new Collection(['app_auth_token' => 'xxx']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertEquals('alipay.open.auth.token.app.query', $payload->get('method'));
        self::assertEquals($params, $payload->get('biz_content'));
        self::assertEquals('query', $payload->get('biz_content')['_action']);
        self::assertFalse($payload->has('app_auth_token'));
    }
}
