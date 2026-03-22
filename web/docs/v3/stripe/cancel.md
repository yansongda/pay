# Stripe 取消支付

:::tip
Stripe 的实现由 GitHub Copilot 生成
:::

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| cancel | array $order | Collection |

## 取消 PaymentIntent

:::tip
取消 PaymentIntent 后，该支付意图将无法再被支付。仅当 PaymentIntent 状态为 `requires_payment_method`、`requires_capture`、`requires_confirmation`、`requires_action` 或 `processing` 时可取消。
:::

```php
Pay::config($config);

$order = [
    'payment_intent_id' => 'pi_3OxxxxxxxxxxxxxxxxxxxxY', // 必填：PaymentIntent ID
    // 「选填」取消原因，可选值：duplicate / fraudulent / requested_by_customer / abandoned
    // 'cancellation_reason' => 'requested_by_customer',
];

$result = Pay::stripe()->cancel($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [取消 PaymentIntent](https://stripe.com/docs/api/payment_intents/cancel)
