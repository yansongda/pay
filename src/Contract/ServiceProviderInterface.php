<?php

namespace Yansongda\Pay\Contract;

interface ServiceProviderInterface
{
    /**
     * register the service.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function register(): void;
}
