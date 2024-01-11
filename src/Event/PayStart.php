<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Event\Event;
use Yansongda\Artful\Rocket;

class PayStart extends Event
{
    /**
     * @var PluginInterface[]
     */
    public array $plugins;

    public array $params;

    public function __construct(array $plugins, array $params, ?Rocket $rocket = null)
    {
        $this->plugins = $plugins;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
