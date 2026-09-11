# 翼支付确认回调

|  方法名   | 参数 |   返回值    |
|:-------:|:---:|:--------:|
| success |  无 | Response |

## 例子

```php
Pay::config($config);

return Pay::bestpay()->success();
```

## 配置参数

无

:::tip
正确处理响应 `success` 后，翼支付才会认为通知成功，否则会按官方契约重发通知。
:::

:::warning
应答格式 `{"resultCode":"SUCCESS","resultMsg":"OK"}` 以翼支付开发者文档
`aggregatePayOrRefundNotify` 契约为准（失败应答 `{"resultCode":"FAILED","resultMsg":"FAILED"}`），
因官方文档需商户登录，建议沙箱联调时复核。
:::
