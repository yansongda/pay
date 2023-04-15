<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;

class WechatServiceProvider implements ServiceProviderInterface
{
    /**
     * @param mixed $data
     *
     * @throws ContainerException
     */
    public function register($data = null): void
    {
        $service = new Wechat();

        Pay::set(Wechat::class, $service);
        Pay::set('wechat', $service);
    }
}
