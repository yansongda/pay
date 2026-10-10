# Apple 退款历史

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
