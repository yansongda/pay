# 日志

SDK 自带日志系统，如果需要指定日志文件或日志级别，请 config 中穿入下列参数。如果不穿入，默认为 `warning` 级别，日志路径在 `sys_get_temp_dir().'/logs/yansongda.pay.log' `

```php
'log' => [
    'file' => './logs/pay.log', // 请注意权限
    'level' => 'debug'
],
```

# 沙箱模式

支付宝及微信均提供了沙箱测试模式，如果需要启动，请 config 中传入下列参数。

```php
'mode' => 'dev',
```



