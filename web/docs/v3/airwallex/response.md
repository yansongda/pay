# 确认 Airwallex 回调

> 官方文档：
> [Listen for webhook events](https://www.airwallex.com/docs/developer-tools/webhooks/listen-for-webhook-events)


处理完 Airwallex Webhook 后，可以直接返回：

```php
return Pay::airwallex()->success();
```

返回内容为：

```json
{"success":true}
```

HTTP 状态码为 `200`。
