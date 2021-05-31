<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use ArrayAccess;
use JsonSerializable as JsonSerializableInterface;
use Psr\Http\Message\RequestInterface;
use Serializable as SerializableInterface;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

class Rocket implements JsonSerializableInterface, SerializableInterface, ArrayAccess
{
    use Accessable;
    use Arrayable;
    use Serializable;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $radar;

    /**
     * @var array
     */
    private $params;

    /**
     * @var \Yansongda\Supports\Collection
     */
    private $payload;

    /**
     * @var \Yansongda\Supports\Collection|\Psr\Http\Message\ResponseInterface
     */
    private $destination;

    public function getRadar(): RequestInterface
    {
        return $this->radar;
    }

    public function setRadar(RequestInterface $radar): Rocket
    {
        $this->radar = $radar;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): Rocket
    {
        $this->params = $params;

        return $this;
    }

    public function getPayload(): Collection
    {
        return $this->payload;
    }

    public function setPayload(Collection $payload): Rocket
    {
        $this->payload = $payload;

        return $this;
    }

    public function mergePayload(array $payload): Rocket
    {
        $this->payload = $this->payload->merge($payload);

        return $this;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|\Yansongda\Supports\Collection
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface|\Yansongda\Supports\Collection $destination
     */
    public function setDestination($destination): Rocket
    {
        $this->destination = $destination;

        return $this;
    }
}
