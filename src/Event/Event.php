<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Pay\Rocket;

class Event
{
    /**
     * @var \Yansongda\Pay\Rocket|null
     */
    public $rocket;

    /**
     * Bootstrap.
     */
    public function __construct(?Rocket $rocket)
    {
        $this->rocket = $rocket;
    }
}
