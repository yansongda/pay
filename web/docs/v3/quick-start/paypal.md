# PayPal 快速入门

:::tip
PayPal 的实现由 GitHub Copilot 生成
:::

在初始化完毕后，就可以直接方便的享受 `yansongda/pay` 带来的便利了。

## Web 支付（创建订单）

```php
Pay::config($config);

$order = [
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'USD',
                'value' => '10.00',
            ],
        ],
    ],
];

$result = Pay::paypal()->web($order);

// 获取用户授权跳转地址并重定向
$approveUrl = collect($result->get('links'))->firstWhere('rel', 'approve')['href'];
```

## Web 支付（捕获订单）

用户在 PayPal 完成授权后，通过 `token` 参数拿到 `order_id`，再调用捕获接口完成扣款。

```php
Pay::config($config);

$result = Pay::paypal()->web([
    '_action' => 'capture',
    'order_id' => $orderId, // 来自 return_url query 参数 `token`
]);
```

## 退款

```php
Pay::config($config);

$result = Pay::paypal()->refund([
    'capture_id' => $captureId, // 捕获支付时返回的 capture id
    'amount' => [
        'value' => '5.00',
        'currency_code' => 'USD',
    ],
]);
```

## 查询订单

```php
Pay::config($config);

$result = Pay::paypal()->query([
    'order_id' => $orderId,
]);
```

## 回调处理

```php
Pay::config($config);

$result = Pay::paypal()->callback();
```

## 响应回调

```php
Pay::config($config);

return Pay::paypal()->success();
```
