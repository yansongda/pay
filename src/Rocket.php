<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use ArrayAccess;
use JsonSerializable as JsonSerializableInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

class Rocket implements JsonSerializableInterface, ArrayAccess
{
    use Accessable;
    use Arrayable;
    use Serializable;

    private ?RequestInterface $radar = null;

    private array $params = [];

    private ?Collection $payload = null;

    private string $packer = PackerInterface::class;

    private string $direction = DirectionInterface::class;

    private null|Collection|MessageInterface $destination = null;

    private null|RequestInterface|ResponseInterface $destinationOrigin = null;

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

    public function setPayload(null|array|Collection $payload): Rocket
    {
        if (is_array($payload)) {
            $payload = new Collection($payload);
        }

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

    public function exceptPayload(mixed $key): Rocket
    {
        if (empty($this->payload)) {
            return $this;
        }

        $this->payload = $this->payload->except($key);

        return $this;
    }

    public function getPacker(): string
    {
        return $this->packer;
    }

    public function setPacker(string $packer): Rocket
    {
        $this->packer = $packer;

        return $this;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): Rocket
    {
        $this->direction = $direction;

        return $this;
    }

    public function getDestination(): null|Collection|MessageInterface
    {
        return $this->destination;
    }

    public function setDestination(null|Collection|MessageInterface $destination): Rocket
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestinationOrigin(): null|RequestInterface|ResponseInterface
    {
        return $this->destinationOrigin;
    }

    public function setDestinationOrigin(null|RequestInterface|ResponseInterface $destinationOrigin): Rocket
    {
        $this->destinationOrigin = $destinationOrigin;

        return $this;
    }

    public function toArray(): array
    {
        $request = $this->getRadar();
        $destination = $this->getDestinationOrigin();

        return [
            'radar' => [
                'url' => $request?->getUri()->__toString(),
                'method' => $request?->getMethod(),
                'headers' => $request?->getHeaders(),
                'body' => (string) $request?->getBody(),
            ],
            'params' => $this->getParams(),
            'payload' => $this->getPayload()?->toArray(),
            'packer' => $this->getPacker(),
            'direction' => $this->getDirection(),
            'destination' => $this->getDestination(),
            'destination_origin' => [
                'status' => $destination instanceof ResponseInterface ? $destination->getStatusCode() : null,
                'headers' => $destination?->getHeaders(),
                'body' => (string) $destination?->getBody(),
            ],
        ];
    }
}
