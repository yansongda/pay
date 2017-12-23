<?php

namespace Yansongda\Gateways\Alipay;

use Yansongda\Supports\Traits\HasHttpRequest;

class Kernel
{
    use HasHttpRequest;

    /**
     * Alipay gateway.
     *
     * @var string
     */
    protected $baseUri = 'https://openapi.alipaydev.com/gateway.do?charset=utf-8';
}
