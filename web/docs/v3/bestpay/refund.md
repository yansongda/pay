# 翼支付退款

|  method |  说明  |      参数      |    返回值     |
|:------:|:----:|:------------:|:----------:|
| refund | 退款 | array $order | Collection |

## 例子

超级收银台（1008）`/integrate/refund`：

```php
Pay::config($this->config);

$result = Pay::bestpay()->refund([
    'outTradeNo' => '1514027114', // 原交易订单号，与 tradeNo 二选一
    'outRequestNo' => 'REFUND001', // 商户退款请求号
    'refundAmt' => '1', // 退款金额，单位：分，支持部分退款
    'requestDate' => date('Y-m-d H:i:s'),
    // 以下字段以商户开通产品联调为准
    'originalTradeDate' => '2026-09-10',
    'operator' => '3178033925245778',
]);

return $result->get('result.refundStatus');
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，具体字段请以翼支付开发者文档
[render.bestpay.cn/open-developers](https://render.bestpay.cn/open-developers/index.html) 为准（需商户登录）。

:::warning
退款为异步类交易，最终结果请以回调（`callback` 中 `notifyType=REFUND`）或查询为准。
:::
