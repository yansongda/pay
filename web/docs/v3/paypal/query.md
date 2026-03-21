# PayPal 查询订单

:::tip
PayPal 的实现由 GitHub Copilot 生成
:::

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

## 查询支付订单

```php
Pay::config($config);

$order = [
    'order_id' => '5O190127TN364715T', // PayPal 订单 ID
    // '_action' => 'order', // 查询订单，默认
];

$result = Pay::paypal()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询订单](https://developer.paypal.com/docs/api/orders/v2/#orders_get)

## 查询退款订单

```php
Pay::config($config);

$order = [
    'refund_id' => '1JU08902781691863', // PayPal 退款 ID
    '_action' => 'refund',
];

$result = Pay::paypal()->query($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询退款](https://developer.paypal.com/docs/api/payments/v2/#refunds_get)
