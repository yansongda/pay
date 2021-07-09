<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Pay\Rocket;

class CallbackReceived extends Event
{
    /**
     * @var string
     */
    public $provider;

    /**
     * @var array|\Psr\Http\Message\ServerRequestInterface|null
     */
    public $contents;

    /**
     * @var array|null
     */
    public $params;

    /**
     * Bootstrap.
     *
     * @param array|\Psr\Http\Message\ServerRequestInterface|null $contents
     */
    public function __construct(string $provider, $contents, ?array $params, ?Rocket $rocket)
    {
        $this->provider = $provider;
        $this->contents = $contents;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
