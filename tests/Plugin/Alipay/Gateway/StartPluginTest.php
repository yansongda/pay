<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Gateway;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\Gateway\StartPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Config;

/**
 * @internal
 *
 * @coversNothing
 */
class StartPluginTest extends TestCase
{
    protected StartPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartPlugin();
    }

    public function testNormal(): void
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('e90dd23a37c5c7b616e003970817ff82', $payload->get('app_cert_sn'));
        self::assertEquals('687b59193f3f462dd5336e5abf83c5d8_02941eef3187dddf3d3b83462e1dfcf6', $payload->get('alipay_root_cert_sn'));
    }

    public function testCustomizedReturnNotifyUrl(): void
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([
            '_return_url' => 'https://yansongda.cn',
            '_notify_url' => 'https://yansongda.cn',
        ]), fn ($rocket) => $rocket);

        self::assertEquals('https://yansongda.cn', $result->getPayload()->get('return_url'));
        self::assertEquals('https://yansongda.cn', $result->getPayload()->get('notify_url'));
    }

    public function testMissingAppPublicCertPath(): void
    {
        Pay::set(ConfigInterface::class, new Config());

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);
        self::expectExceptionMessage('配置异常: 缺少支付宝配置 -- [app_public_cert_path]');

        $this->plugin->assembly(new Rocket(), fn ($rocket) => $rocket);
    }
}
