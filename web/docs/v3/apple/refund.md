# Apple 退款历史

:::warning
Apple 的实现由 AI 生成并经人工审核，但由于测试资源限制，尚未经过实际测试，请在充分验证后再用于生产环境。
:::

| 方法名 | 参数 | 返回值 |
|:---:|:---:|:---:|
| refund | array $order | Collection |

:::warning
Apple 无服务端主动退款接口，`refund` 实际为退款历史查询（`GET /inApps/v2/refund/lookup/{anyTransactionId}`）。响应中的 `signedTransactions` 为内嵌 JWS，SDK 原样返回，业务方如有需要可自行验签。
:::

## 例子

```php
Pay::config($this->config);

$result = Pay::apple()->refund([
    'transaction_id' => '1234567890',
]);
```
