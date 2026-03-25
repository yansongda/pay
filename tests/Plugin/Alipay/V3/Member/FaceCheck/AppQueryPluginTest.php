<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\FaceCheck;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\FaceCheck\AppQueryPlugin;
use Yansongda\Pay\Tests\TestCase;

class AppQueryPluginTest extends TestCase
{
    protected AppQueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AppQueryPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'zim_id' => 'zoloz_zim_id',
        ]), fn ($rocket) => $rocket);

        self::assertSame('GET', $result->getPayload()->get('_method'));
        self::assertSame('/v3/datadigital/fincloud/generalsaas/face/check/query?zim_id=zoloz_zim_id', $result->getPayload()->get('_url'));
        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
