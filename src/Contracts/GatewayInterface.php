<?php

namespace Yansongda\Pay\Contracts;

interface GatewayInterface
{
    public function pay($endpoint, array $payload);
}
