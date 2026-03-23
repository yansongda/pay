# Stripe 确认回调

:::tip
Stripe 的实现由 GitHub Copilot 生成
:::

|   方法名   | 参数  |   返回值    |
|:-------:|:---:|:--------:|
| success |  无  | Response |

## 例子

```php
Pay::config($config);

// $result = Pay::stripe()->callback();

return Pay::stripe()->success();
```

## 订单配置参数

无
