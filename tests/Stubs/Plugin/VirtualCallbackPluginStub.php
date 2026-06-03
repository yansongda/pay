<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs\Plugin;

use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Plugin\Wechat\Virtual\CallbackPlugin;

class VirtualCallbackPluginStub extends CallbackPlugin
{
    protected function verifySign(WechatConfig $config, string $encrypt, string $signature, string $timestamp, string $nonce): void
    {
    }

    protected function decryptMessage(WechatConfig $config, string $encrypt): array
    {
        return [
            'EventType' => 'OpenProductBuy',
            'OutTradeNo' => 'test-order-no',
            'OfferId' => '1234567890',
            'ProductId' => 'test-product-id',
            'Quantity' => 1,
        ];
    }
}
