<?php

namespace Yansongda\Pay;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Events
{
    /**
     * Start pay.
     *
     * @Event("Yansongda\Pay\Events\StartingPay")
     */
    const STARTING_PAY = 'yansongda.pay.starting';

    /**
     * Before pay.
     *
     * @Event("Yansongda\Pay\Events\PayBefore")
     */
    const BEFORE_PAY = 'yansongda.pay.before';

    /**
     * Paying.
     *
     * @Event("Yansongda\Pay\Events\ApiRequeting")
     */
    const API_REQUESTING = 'yansongda.pay.api.requesting';

    /**
     * Paid.
     *
     * @Event("Yansongda\Pay\Events\ApiRequeted")
     */
    const API_REQUESTED = 'yansongda.pay.api.requested';

    /**
     * Sign error.
     *
     * @Event("Yansongda\Pay\Events\WrongSign")
     */
    const SIGN_FAILED = 'yansongda.pay.sign.failed';

    /**
     * Receive request.
     *
     * @Event("Yansongda\Pay\Events\RequestReceived")
     */
    const REQUEST_RECEIVED = 'yansongda.pay.request.received';

    /**
     * Method called.
     *
     * @Event("Yansongda\Pay\Events\MethodCalled")
     */
    const METHOD_CALLED = 'yansongda.pay.method.called';

    /**
     * dispatcher.
     *
     * @var EventDispatcher
     */
    protected static $dispatcher;

    /**
     * Forward call.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::getDispatcher(), $method], $args);
    }

    /**
     * Forward call.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([self::getDispatcher(), $method], $args);
    }

    /**
     * setDispatcher.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param EventDispatcher $dispatcher
     *
     * @return void
     */
    public static function setDispatcher(EventDispatcher $dispatcher)
    {
        self::$dispatcher = $dispatcher;
    }

    /**
     * getDispatcher.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return EventDispatcher
     */
    public static function getDispatcher(): EventDispatcher
    {
        if (self::$dispatcher) {
            return self::$dispatcher;
        }

        return self::$dispatcher = self::createDispatcher();
    }

    /**
     * createDispatcher.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return EventDispatcher
     */
    public static function createDispatcher(): EventDispatcher
    {
        return new EventDispatcher();
    }
}
