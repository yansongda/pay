# Apple 确认回调

:::warning
Apple 的实现由 AI 生成并经人工审核，但由于测试资源限制，尚未经过实际测试，请在充分验证后再用于生产环境。
:::

| 方法名 | 参数 | 返回值 |
|:---:|:---:|:---:|
| success | 无 | Response |

## 例子

```php
Pay::config($this->config);

return Pay::apple()->success();
```

## 配置参数

无。应答内容为固定 JSON：`{"result":"success"}`。
