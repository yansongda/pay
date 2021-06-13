<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class Wechat extends AbstractProvider
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://api.mch.weixin.qq.com/',
        Pay::MODE_SANDBOX => 'https://api.mch.weixin.qq.com/sandboxnew/',
        Pay::MODE_SERVICE => 'https://api.mch.weixin.qq.com/',
    ];

    public function find($order): Collection
    {
    }

    public function cancel($order): Collection
    {
        // TODO: Implement cancel() method.
    }

    public function close($order): Collection
    {
        // TODO: Implement close() method.
    }

    public function refund(array $order): Collection
    {
        // TODO: Implement refund() method.
    }

    public function verify($contents = null, ?array $params = null): Collection
    {
        // TODO: Implement verify() method.
    }

    public function success(): ResponseInterface
    {
        // TODO: Implement success() method.
    }
}
