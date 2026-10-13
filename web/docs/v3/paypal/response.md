# PayPal 确认回调

:::warning
PayPal 的实现由 AI 生成并经人工审核，但由于测试资源限制，尚未经过实际测试，请在充分验证后再用于生产环境。
:::

|   方法名   | 参数  |   返回值    |
|:-------:|:---:|:--------:|
| success |  无  | Response |

## 例子

```php
Pay::config($config);

// $result = Pay::paypal()->callback();

return Pay::paypal()->success();
```

## 订单配置参数

无
