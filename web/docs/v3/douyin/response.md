# 抖音确认回调

|   方法名   | 参数  |   返回值    |
|:-------:|:---:|:--------:|
| success |  无  | Response |

## 例子

```php
Pay::config($config);

// $result = Pay::douyin()->callback($request);

return Pay::douyin()->success();
```

## 说明

`success()` 返回 HTTP 200 的 JSON 应答体，用于告知抖音平台已成功接收回调：

```json
{"err_no": 0, "err_tips": "success"}
```

## err_no 语义

抖音通用交易系统各接口响应与回调应答均使用顶层 `err_no` 表示业务处理结果：

- `err_no = 0`：成功
- `err_no != 0`：失败，错误信息取顶层 `err_msg`（trade_basic 业务接口）或 `err_tips`（回调应答/退款申请应答），两套格式并存

SDK 对业务接口的响应已统一校验 `err_no === 0`，非 0 时抛出异常，业务代码中无需重复判断；回调应答（`success()` 及退款申请回调的同步应答）需业务方自行保证 `err_no = 0`。
