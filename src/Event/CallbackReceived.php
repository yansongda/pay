<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Event\Event;
use Yansongda\Artful\Rocket;

class CallbackReceived extends Event
{
    public string $provider;

    /**
     * @var null|array<string, mixed>
     */
    public ?array $params = null;

    /**
     * @var null|array<string, mixed>|ServerRequestInterface
     */
    public array|ServerRequestInterface|null $contents;

    /**
     * @param null|array<string, mixed>|ServerRequestInterface $contents
     * @param null|array<string, mixed>                        $params
     */
    public function __construct(string $provider, array|ServerRequestInterface|null $contents, ?array $params = null, ?Rocket $rocket = null)
    {
        $this->provider = $provider;
        $this->contents = $contents;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
