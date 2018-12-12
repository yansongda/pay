# 事件系统

> v2.6.0-beta.1 及以上可用

在支付过程中，可能会想监听一些事件，好同时处理一些其它任务。

SDK 使用 [symfony/event-dispatcher](https://github.com/symfony/event-dispatcher) 组件进行事件的相关操作。

## 所有事件说明

- yansongda.pay.starting (Yansongda\Pay\Events\PayStarting)
    
    - 事件类：Yansongda\Pay\Events\PayStarting::class
    - 说明：此事件将在最开始进行支付时进行抛出。此时 SDK 只进行了相关初始化操作，其它所有操作均未开始。
    
- yansongda.pay.started (Yansongda\Pay\Events\PayStarted)

    - 事件类：Yansongda\Pay\Events\PayStarted
    - 说明：此事件将在所有参数处理完毕时抛出。

- yansongda.pay.api.requesting (Yansongda\Pay\Events\ApiRequesting)

    - 事件类：Yansongda\Pay\Events\ApiRequesting
    - 说明：此事件将在请求支付方的 API 前抛出。
        
- yansongda.pay.api.requested (Yansongda\Pay\Events\ApiRequested)

    - 事件类：Yansongda\Pay\Events\ApiRequested
    - 说明：此事件将在请求支付方的 API 完成之后抛出。
        
- yansongda.pay.sign.failed (Yansongda\Pay\Events\WrongSign)
    
    - 事件类：Yansongda\Pay\Events\WrongSign
    - 说明：此事件将在签名验证失败时抛出。
    
- yansongda.pay.request.received (Yansongda\Pay\Events\RequestReceived)
    
    - 事件类：Yansongda\Pay\Events\RequestReceived
    - 说明：此事件将在收到支付方的请求（通常在异步通知或同步通知）时抛出
    
- yansongda.pay.method.called (Yansongda\Pay\Events\MethodCalled)
    
    - 事件类：Yansongda\Pay\Events\MethodCalled
    - 说明：此事件将在调用除 PAYMETHOD 方法（例如，查询订单，退款，取消订单）时抛出
