# 通联支付确认回调

|   方法名   | 参数  |   返回值    |
|:-------:|:---:|:--------:|
| success |  无  | Response |

## 例子

```php
Pay::config($config);

return Pay::allinpay()->success();
```

## 配置参数

无

:::tip
正确处理响应 `success` 后，通联支付才会认为通知成功，
否则会按 15s/15s/5m/10m/15m/20m/25m/30m 的频率重发 8 次。
:::
