<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use JsonSerializable as JsonSerializableInterface;
use Serializable as SerializableInterface;
use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

class Request extends \GuzzleHttp\Psr7\Request implements JsonSerializableInterface, SerializableInterface
{
    use Accessable;
    use Arrayable;
    use Serializable;

    public function toArray(): array
    {
        return [
            'url' => $this->getUri()->__toString(),
            'method' => $this->getMethod(),
            'headers' => $this->getHeaders(),
            'body' => $this->getBody()->getContents(),
        ];
    }
}
