<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;
use Yansongda\Pay\Rocket;

class Event extends SymfonyEvent
{
    /**
     * Driver.
     *
     * @var string
     */
    public $provider;

    /**
     * @var \Yansongda\Pay\Rocket|null
     */
    public $rocket;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function __construct(?Rocket $rocket)
    {
        $this->rocket = $rocket;
    }
}
