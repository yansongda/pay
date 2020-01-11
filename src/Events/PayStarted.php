<?php

namespace Yansongda\Pay\Events;

class PayStarted extends Event
{
    /**
     * Endpoint.
     *
     * @var string
     */
    public $endpoint;

    /**
     * Payload.
     *
     * @var array
     */
    public $payload;

    /**
     * Bootstrap.
     */
    public function __construct(string $driver, string $gateway, string $endpoint, array $payload)
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;

        parent::__construct($driver, $gateway);
    }
}
