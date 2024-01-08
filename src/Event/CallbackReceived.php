<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Event\Event;
use Yansongda\Artful\Rocket;

class CallbackReceived extends Event
{
    public string $provider;

    public ?array $params = null;

    public null|array|ServerRequestInterface $contents;

    public function __construct(string $provider, null|array|ServerRequestInterface $contents, ?array $params = null, ?Rocket $rocket = null)
    {
        $this->provider = $provider;
        $this->contents = $contents;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
