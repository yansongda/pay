# 事件使用

::: tip
使用事件系统前，确保已初始化 pay。即调用了 `Pay::xxx($config)`
:::

```php
<?php

use Yansongda\Pay\Events;
use Yansongda\Pay\Events\PayStarting;

// 1. 新建一个监听器
class PayStartingListener
{
    public function sendEmail(PayStarting $event)
    {
        // 可以直接通过 $event 获取事件的额外数据，例如：
        //      支付提供商： $event->driver   // alipay/wechat
        //      支付 gateway：$event->gateway  // app/web/pos/scan ...
        //      支付传递的参数：$event->params
        
        // coding to send email...
    }
}

// 2. 添加监听器
Events::addListener(PayStarting::class, [new PayStartingListener(), 'sendEmail']);

// 3. 喝杯咖啡

```
