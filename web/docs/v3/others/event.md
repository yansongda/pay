# 事件

在支付过程中，可能会想监听一些事件，好同时处理一些其它任务。

SDK 使用 [symfony/event-dispatcher](https://github.com/symfony/event-dispatcher) 组件进行事件的相关操作。

在使用之前，需要先确保安装了 `symfony/event-dispatcher` 组件，如果没有，请安装

```shell
composer require symfony/event-dispatcher
```

## 使用

::: tip
使用事件系统前，确保已初始化 pay。即调用了 `Pay::config($config)`
:::

```php
<?php

use Yansongda\Pay\Event;
use Yansongda\Pay\Event\PayStarted;

// 1. 新建一个监听器
class PayStartedListener
{
    public function sendEmail(PayStarted $event)
    {
        // 可以直接通过 $event 获取事件的额外数据，例如：
        //      支付传递的参数：$event->params
        
        // coding to send email...
    }
}

// 2. 添加监听器
Event::addListener(PayStarted::class, [new PayStartedListener(), 'sendEmail']);

// 3. 喝杯咖啡
```

## 事件

### 支付开始

- 事件类：Yansongda\Pay\Event\PayStarted
- 说明：此事件将在支付进入核心流程时进行抛出。此时 SDK 只进行了相关初始化操作，其它所有操作均未开始。
- 额外数据：
    - $rocket (相关参数)
    - $plugins (所有使用的插件)
    - $params (传递的原始参数)

### 支付完毕

- 事件类：Yansongda\Pay\Event\PayFinish
- 说明：此事件将在所有参数处理完毕时抛出。
- 额外数据：
    - $rocket (相关参数)

### 开始调用API

- 事件类：Yansongda\Pay\Event\ApiRequesting
- 说明：此事件将在请求支付方的 API 前抛出。
- 额外数据：
    - $rocket (相关参数)

### 调用API结束

- 事件类：Yansongda\Pay\Event\ApiRequested
- 说明：此事件将在请求支付方的 API 完成之后抛出。
- 额外数据：
    - $rocket (相关参数)

### 收到通知

- 事件类：Yansongda\Pay\Event\CallbackReceived
- 说明：此事件将在收到支付方的请求（通常在异步通知或同步通知）时抛出
- 额外数据：
    - $provider (支付机构)
    - $contents (收到的数据)
    - $params (自定义数据)
    
### 调用其它方法

- 事件类：Yansongda\Pay\Event\MethodCalled
- 说明：此事件将在调用除 PAYMETHOD 方法（例如，查询订单，退款，取消订单）时抛出
- 额外数据：
    - $provider (支付机构)
    - $name (调用方法)
    - $params (参数)
