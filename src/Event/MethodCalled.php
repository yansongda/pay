<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

class MethodCalled extends Event
{
    /**
     * endpoint.
     *
     * @var string
     */
    public $endpoint;

    /**
     * payload.
     *
     * @var array
     */
    public $payload;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function __construct(string $driver, string $gateway, string $endpoint, array $payload = [])
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;

        parent::__construct($driver, $gateway);
    }
}
