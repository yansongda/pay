<?php

namespace Yansongda\Pay\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Yansongda\Pay\Events;

class KernelSubscriber implements EventSubscriberInterface
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
    public static function getSubscribedEvents ()
    {
        return [
            Events::STARTING_PAY => [],
            Events::BEFORE_PAY => [],
            Events::API_REQUESTING => [],
            Events::API_REQUESTED => [],
            Events::SIGN_FAILED => [],
            Events::REQUEST_RECEIVED => [],
            Events::METHOD_CALLED => []
        ];
    }

    public function WriteLog()
    {

    }
}
