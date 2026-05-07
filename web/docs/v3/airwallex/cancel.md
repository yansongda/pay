# Airwallex 取消

> 官方文档：
> [Cancellations](https://www.airwallex.com/docs/api/payments/payment_intents/cancel)


| 方法名 | 参数 | 返回值 |
|:--:|:--:|:--:|
| cancel | array $order | Collection |

## 例子

```php
Pay::config($config);

$result = Pay::airwallex()->cancel([
    'payment_intent_id' => 'int_xxx',
    // 'cancellation_reason' => 'requested_by_customer',
]);
```

也可以直接传 `id`：

```php
$result = Pay::airwallex()->cancel([
    'id' => 'int_xxx',
]);
```

:::warning
Airwallex 的取消通常适用于可取消的 Payment Intent，
具体以 Airwallex 官方支付状态规则为准。
:::
