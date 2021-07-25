<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use ArrayAccess;
use JsonSerializable as JsonSerializableInterface;
use Psr\Http\Message\MessageInterface;
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
     * @var \Psr\Http\Message\RequestInterface|null
     */
    private $radar = null;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var \Yansongda\Supports\Collection|null
     */
    private $payload = null;

    /**
     * @var string|null
     */
    private $direction = null;

    /**
     * @var \Yansongda\Supports\Collection|\Psr\Http\Message\MessageInterface|array|null
     */
    private $destination = null;

    /**
     * @var \Psr\Http\Message\MessageInterface|null
     */
    private $destinationOrigin = null;

    public function getRadar(): ?RequestInterface
    {
        return $this->radar;
    }

    public function setRadar(?RequestInterface $radar): Rocket
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

    public function mergeParams(array $params): Rocket
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function getPayload(): ?Collection
    {
        return $this->payload;
    }

    public function setPayload(?Collection $payload): Rocket
    {
        $this->payload = $payload;

        return $this;
    }

    public function mergePayload(array $payload): Rocket
    {
        if (empty($this->payload)) {
            $this->payload = new Collection();
        }

        $this->payload = $this->payload->merge($payload);

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): Rocket
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return \Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|array|null
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param \Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|array|null $destination
     */
    public function setDestination($destination): Rocket
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestinationOrigin(): ?MessageInterface
    {
        return $this->destinationOrigin;
    }

    public function setDestinationOrigin(?MessageInterface $destinationOrigin): Rocket
    {
        $this->destinationOrigin = $destinationOrigin;

        return $this;
    }
}
