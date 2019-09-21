<?php

namespace Yansongda\Pay\Service;

use Pimple\Container;
use Yansongda\Pay\Contract\ServiceProviderInterface;

class WechatServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.
    }
}
