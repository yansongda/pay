# Airwallex 关闭

> 官方文档：
> [API Introduction](https://www.airwallex.com/docs/api/introduction)


:::danger
当前 Airwallex Provider 未实现 `close()`。
:::

如果你调用：

```php
Pay::airwallex()->close($order);
```

将会抛出 `参数异常: Airwallex 不支持 close API`。

如需终止支付，请根据实际场景使用 `cancel()`。
