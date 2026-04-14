# Airwallex 支付

Airwallex 当前内置支持以下支付方式：

| method | 说明 | 参数 | 返回值 |
|:--:|:--:|:--:|:--:|
| intent | Payment Intent 初始化支付 | array $order | Collection |

## Payment Intent 支付

适用于服务端发起 Airwallex 支付的场景。

当前 `intent()` 会在 SDK 内部自动完成以下流程：

1. 调用 `/api/v1/pa/payment_intents/create` 创建 Payment Intent
2. 解析创建结果中的 `id`
3. 调用 `/api/v1/pa/payment_intents/{id}/confirm` 确认支付

### 例子

```php
Pay::config($config);

$order = [
    'amount' => 100,
    'currency' => 'USD',
    'merchant_order_id' => 'order_20260414_001',
    // 'return_url' => 'https://example.com/airwallex/return',
    // 'customer_id' => 'cus_xxx',
    // 'payment_method' => [
    //     'type' => 'card',
    // ],
    // 'payment_method_options' => [
    //     'card' => [
    //         'auto_capture' => true,
    //     ],
    // ],
    // 'metadata' => [
    //     'biz_order_no' => 'A20260414001',
    // ],
];

$result = Pay::airwallex()->intent($order);
```

### 订单配置参数

当前 SDK 会将常见参数透传到创建与确认流程中，例如：

- `amount`
- `currency`
- `merchant_order_id`
- `return_url`
- `payment_method`
- `payment_method_options`
- `customer_id`
- `customer`
- `descriptor`
- `order`
- `metadata`

如需了解完整参数说明，请参考 [Airwallex Payment Intents 文档](https://www.airwallex.com/docs/payments/get-started/quickstart)。

:::warning
当前 SDK 默认执行的是 `create + confirm`。

不同支付方式在 `confirm` 之后可能仍然会出现用户跳转、额外授权、异步通知等后续动作，
请根据 Airwallex 官方支付流程继续在业务层处理。
:::
