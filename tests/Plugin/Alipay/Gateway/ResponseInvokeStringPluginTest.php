<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Gateway;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\Gateway\ResponseInvokeStringPlugin;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ResponseInvokeStringPluginTest extends TestCase
{
    private ResponseInvokeStringPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponseInvokeStringPlugin();
    }

    public function testNormal(): void
    {
        $payload = [
            'name' => 'yansongda',
            'age' => 30,
        ];

        $result = $this->plugin->assembly((new Rocket())->mergePayload($payload), fn ($rocket) => $rocket);

        self::assertEquals(http_build_query($payload), $result->getDestination()->getBody()->getContents());
    }
}
