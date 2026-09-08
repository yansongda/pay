# 抖音关闭订单

|  方法名  |      参数      | 返回值  |
|:-----:|:------------:|:----:|
| close | array $order |  /   |

:::danger
抖音官方（通用交易系统）无关闭订单 API，如有退款需求，可使用 `refund` 方法。
:::

## 异常

调用该方法会直接抛出异常：

Yansongda\Artful\Exception\InvalidParamsException
