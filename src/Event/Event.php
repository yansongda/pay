<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

use Yansongda\Pay\Rocket;

class Event
{
    public ?Rocket $rocket;

    public function __construct(?Rocket $rocket)
    {
        $this->rocket = $rocket;
    }
}
