<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;

class WechatServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register(Pay $pay, ?array $data = null): void
    {
        $service = function () {
            Pay::set(ParserInterface::class, CollectionParser::class);

            return new Wechat();
        };

        $pay::set(Wechat::class, $service);
        $pay::set('wechat', $service);
    }
}
