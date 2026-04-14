# 确认 Airwallex 回调

处理完 Airwallex Webhook 后，可以直接返回：

```php
return Pay::airwallex()->success();
```

返回内容为：

```json
{"success":true}
```

HTTP 状态码为 `200`。
