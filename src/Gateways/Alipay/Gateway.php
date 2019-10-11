<?php
/**
 * Created by pay-2.8.6.
 * Author: iwzh <wzhec@foxmail.com>
 * Date: 2019/10/11 14:16
 */

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Supports\Collection;

abstract class Gateway implements GatewayInterface
{
    /**
     * Mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * Gateway constructor.
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->mode = Support::getInstance()->mode;
    }

    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Collection
     */
    abstract public function pay($endpoint, array $payload);

}