<?php

namespace Yansongda\Pay\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Yansongda\Pay\Events;
use Yansongda\Pay\Log;

class KernelLogSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PAY_STARTING => ['writePayStartingLog', 256],
            Events::PAY_STARTED => ['writePayStartedLog', 256],
            Events::API_REQUESTING => ['writeApiRequestingLog', 256],
            Events::API_REQUESTED => ['writeApiRequestedLog', 256],
            Events::SIGN_FAILED => ['writeSignFailedLog', 256],
            Events::REQUEST_RECEIVED => ['writeRequestReceivedLog', 256],
            Events::METHOD_CALLED => ['writeMethodCalledLog', 256]
        ];
    }

    /**
     * writePayStartingLog.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Events\PayStarting $event
     *
     * @return void
     */
    public function writePayStartingLog(Events\PayStarting $event)
    {
        Log::debug("Starting To {$event->driver}", [$event->gateway, $event->params]);
    }

    /**
     * writePayStartedLog.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Events\PayStarted $event
     *
     * @return void
     */
    public function writePayStartedLog(Events\PayStarted $event)
    {
        Log::info(
            "{$event->driver} {$event->gateway} Drivers Started",
            [$event->endpoint, $event->payload]
        );
    }

    public function writeApiRequestingLog(Events\ApiRequesting $event)
    {
    }

    public function writeApiRequestedLog(Events\ApiRequested $event)
    {
    }

    public function writeSignFailedLog(Events\SignFailed $event)
    {
    }

    public function writeRequestReceivedLog(Events\RequestReceived $event)
    {
    }

    public function writeMethodCalledLog(Events\MethodCalled $event)
    {
    }
}
