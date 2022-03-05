<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Pay\Rocket;

class CallbackReceived extends Event
{
    public string $provider;

    public ?array $params = null;

    /**
     * @var array|\Psr\Http\Message\ServerRequestInterface|null
     */
    public $contents;

    /**
     * @param array|\Psr\Http\Message\ServerRequestInterface|null $contents
     */
    public function __construct(string $provider, $contents, ?array $params = null, ?Rocket $rocket = null)
    {
        $this->provider = $provider;
        $this->contents = $contents;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
