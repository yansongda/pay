<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Yansongda\Pay\Pay;

interface ServiceProviderInterface
{
    /**
     * prepare something.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function prepare(array $data): void;

    /**
     * register the service.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function register(Pay $pay): void;
}
