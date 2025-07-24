<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Artful\Event\ArtfulEnd;
use Yansongda\Artful\Event\ArtfulStart;
use Yansongda\Artful\Event\HttpEnd;
use Yansongda\Artful\Event\HttpStart;
use Yansongda\Pay\Event\PayEnd;
use Yansongda\Pay\Event\PayStart;

class PayListener
{
    public static function artfulStart(ArtfulStart $event): void
    {
        Event::dispatch(new PayStart($event->plugins, $event->params));
    }

    public static function artfulEnd(ArtfulEnd $event): void
    {
        Event::dispatch(new PayEnd($event->rocket));
    }

    public static function httpStart(HttpStart $event): void
    {
        Event::dispatch(new Event\HttpStart($event->rocket));
    }

    public static function httpEnd(HttpEnd $event): void
    {
        Event::dispatch(new Event\HttpEnd($event->rocket));
    }
}
