# Stripe 查询订单

:::tip
Stripe 的实现由 GitHub Copilot 生成
:::

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

## 查询支付订单

```php
Pay::config($config);

$order = [
    'payment_intent_id' => 'pi_3OxxxxxxxxxxxxxxxxxxxxY', // Stripe PaymentIntent ID
    // '_action' => 'order', // 查询支付订单，默认
];

$result = Pay::stripe()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询 PaymentIntent](https://stripe.com/docs/api/payment_intents/retrieve)

## 查询退款订单

```php
Pay::config($config);

$order = [
    'refund_id' => 're_3OxxxxxxxxxxxxxxxxxxxxY', // Stripe 退款 ID
    '_action' => 'refund',
];

$result = Pay::stripe()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询退款](https://stripe.com/docs/api/refunds/retrieve)
