# Airwallex 查询

> 官方文档：
> [Retrieve PaymentIntent](https://www.airwallex.com/docs/api/payments/payment_intents/retrieve)
> [Retrieve Refund](https://www.airwallex.com/docs/api/payments/refunds/retrieve)


| 方法名 | 参数 | 返回值 |
|:--:|:--:|:--:|
| query | array $order | Collection |

## 查询 Payment Intent

```php
Pay::config($config);

$result = Pay::airwallex()->query([
    'payment_intent_id' => 'int_xxx',
]);
```

也可以直接传 `id`：

```php
$result = Pay::airwallex()->query([
    'id' => 'int_xxx',
]);
```

## 查询退款

```php
$result = Pay::airwallex()->query([
    '_action' => 'refund',
    'refund_id' => 'ref_xxx',
]);
```

也可以直接传 `id`：

```php
$result = Pay::airwallex()->query([
    '_action' => 'refund',
    'id' => 'ref_xxx',
]);
```
