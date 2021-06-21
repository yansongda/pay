<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Pay\Rocket;

class PayStarted extends Event
{
    /**
     * @var \Yansongda\Pay\Contract\PluginInterface[]
     */
    public $plugins;

    /**
     * @var array
     */
    public $params;

    public function __construct(array $plugins, array $params, ?Rocket $rocket)
    {
        $this->plugins = $plugins;
        $this->params = $params;

        parent::__construct($rocket);
    }
}
