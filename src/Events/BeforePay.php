<?php

namespace Yansongda\Pay\Events;

use Symfony\Component\EventDispatcher\Event;

class BeforePay extends Event
{
    /**
     * Driver.
     *
     * @var string
     */
    public $driver;

    /**
     * Method.
     *
     * @var string
     */
    public $method;

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
     *
     * @param string $driver
     * @param string $method
     * @param string $endpoint
     * @param array  $payload
     */
    public function __construct(string $driver, string $method, string $endpoint, array $payload)
    {
        $this->driver = $driver;
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->payload = $payload;
    }
}
