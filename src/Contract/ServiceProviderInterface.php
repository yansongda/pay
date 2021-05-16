<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Yansongda\Pay\Pay;

interface ServiceProviderInterface
{
    /**
     * register the service.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function register(Pay $pay, ?array $data = null): void;
}
