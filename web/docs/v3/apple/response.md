# Apple 确认回调

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
