# 翼支付关闭订单

|  method |   说明   |      参数      |    返回值     |
|:------:|:------:|:------------:|:----------:|
| close  | 关闭订单 | array $order | Collection |

## 例子

超级收银台（1008）`/pay/closeOrder`：

```php
Pay::config($this->config);

$result = Pay::bestpay()->close([
    'outTradeNo' => '1514027114', // 与 tradeNo 二选一
]);

return $result;
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，具体字段请以翼支付开发者文档
[render.bestpay.cn/open-developers](https://render.bestpay.cn/open-developers/index.html) 为准（需商户登录）。

:::warning
翼支付官方无对等 `cancel`（撤销）接口，调用 `Pay::bestpay()->cancel()` 会抛出异常；
如需撤销请以退款（`refund`）方式处理。
:::
