# 事件说明

## 支付开始
    
- 事件类：Yansongda\Pay\Events\PayStarting::class
- 说明：此事件将在最开始进行支付时进行抛出。此时 SDK 只进行了相关初始化操作，其它所有操作均未开始。
- 额外数据：
    - $driver (支付机构)
    - $gateway (支付网关)
    - $params (传递的原始参数)
    

## 支付初始化完毕

- 事件类：Yansongda\Pay\Events\PayStarted
- 说明：此事件将在所有参数处理完毕时抛出。
- 额外数据：
    - $driver (支付机构)
    - $gateway (支付网关)
    - $endpoint (支付的 url endpoint)
    - $payload (数据)


## 开始调用API

- 事件类：Yansongda\Pay\Events\ApiRequesting
- 说明：此事件将在请求支付方的 API 前抛出。
- 额外数据：
    - $driver (支付机构)
    - $gateway (支付网关)
    - $endpoint (支付的 url endpoint)
    - $payload (数据)
        

## 调用API结束

- 事件类：Yansongda\Pay\Events\ApiRequested
- 说明：此事件将在请求支付方的 API 完成之后抛出。
- 额外数据：
    - $driver (支付机构)
    - $gateway (支付网关)
    - $endpoint (支付的 url endpoint)
    - $result (请求后的返回数据)
        

## 验签失败
    
- 事件类：Yansongda\Pay\Events\SignFailed
- 说明：此事件将在签名验证失败时抛出。
- 额外数据：
    - $driver (支付机构)
    - $gateway (支付网关)
    - $data (验签数据)
    

## 收到通知
    
- 事件类：Yansongda\Pay\Events\RequestReceived
- 说明：此事件将在收到支付方的请求（通常在异步通知或同步通知）时抛出
- 额外数据：
    - $driver (支付机构)
    - $gateway (支付网关)
    - $data (收到的数据)
    

## 调用其它方法
    
- 事件类：Yansongda\Pay\Events\MethodCalled
- 说明：此事件将在调用除 PAYMETHOD 方法（例如，查询订单，退款，取消订单）时抛出
- 额外数据：
    - $driver (支付机构)
    - $gateway (调用方法)
    - $endpoint (支付的 url endpoint)
    - $payload (数据)
