<?php

namespace Yansongda\Pay\Contract;

use Yansongda\Pay\Pay;

interface ServiceProviderInterface
{
    /**
     * register the service.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function register(Pay $pay): void;
}
