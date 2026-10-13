# Stripe 关闭订单

:::warning
Stripe 的实现由 AI 生成并经人工审核，但由于测试资源限制，尚未经过实际测试，请在充分验证后再用于生产环境。
:::

:::danger
Stripe 官方无此 API，如有取消支付需求，可使用 `cancel` 方法；如有退款需求，可使用 `refund` 方法。
:::

## 异常

Yansongda\Pay\Exceptions\InvalidParamsException
