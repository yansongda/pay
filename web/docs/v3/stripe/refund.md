# Stripe 退款

:::tip
Stripe 的实现由 GitHub Copilot 生成
:::

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款操作

:::tip
Stripe 退款基于已完成支付的 PaymentIntent 或 Charge 发起，退款时需要提供 `payment_intent` 或 `charge`。
:::

```php
Pay::config($config);

$order = [
    'payment_intent' => 'pi_3OxxxxxxxxxxxxxxxxxxxxY', // 必填（与 charge 二选一）：PaymentIntent ID
    // 'charge' => 'ch_3OxxxxxxxxxxxxxxxxxxxxY',       // 必填（与 payment_intent 二选一）：Charge ID
    // 「选填」部分退款时传入，单位为最小货币单位，不传则全额退款
    // 'amount' => 500,
    // 「选填」退款原因，可选值：duplicate / fraudulent / requested_by_customer
    // 'reason' => 'requested_by_customer',
];

$result = Pay::stripe()->refund($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [退款](https://stripe.com/docs/api/refunds/create)
