<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Member\Certification;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Member\Certification\InitPlugin;
use Yansongda\Pay\Tests\TestCase;

class InitPluginTest extends TestCase
{
    protected InitPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InitPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            'identity_param' => ['identity_type' => 'CERT_INFO'],
        ]), fn ($rocket) => $rocket);

        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/user/certify/open/initialize', $result->getPayload()->get('_url'));
        self::assertSame('FACE', $result->getPayload()->get('_body')['product_code']);
        self::assertSame(['identity_type' => 'CERT_INFO'], $result->getPayload()->get('_body')['identity_param']);
    }
}
