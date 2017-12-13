<h1 align="center">Pay</h1>

<p align="center">
<a href="https://styleci.io/repos/100355112"><img src="https://styleci.io/repos/100355112/shield?branch=master" alt="StyleCI"></a>
<a href="https://scrutinizer-ci.com/g/yansongda/pay/?branch=master"><img src="https://scrutinizer-ci.com/g/yansongda/pay/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
<a href="https://scrutinizer-ci.com/g/yansongda/pay/build-status/master"><img src="https://scrutinizer-ci.com/g/yansongda/pay/badges/build.png?b=master" alt="Build Status"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/license" alt="License"></a>
</p>

开发了多次支付宝与微信支付后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

**说明，请先熟悉 支付宝/微信支付 开发文档！！**

欢迎 Star，欢迎 PR！

laravel 扩展包请 [传送至这里](https://github.com/yansongda/laravel-pay)


## 特点
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 符合 PSR 标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的


## 运行环境
- PHP 7.0+
- composer

> PHP 5.6+ 版本请下载 v1.x 版


## 安装
```shell
composer require yansongda/pay
```

> PHP 5.6+ 版本请下载 v1.x 版


## 支持的支付环境
- pay(array $config_biz)  
说明：支付接口  

- refund(array|string $config_biz, $refund_amount = null)  
说明：退款接口  

- close(array|string $config_biz)  
说明：关闭订单接口  

- find(string $out_trade_no)  
说明：查找订单接口  

- verify($data, $sign = null)  
说明：验证服务器返回消息是否合法  

### 1、支付宝
| driver | gateway |   描述       |
| :----: | :-----: | :-------:   |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |
| alipay | app     | APP 支付  |
| alipay | pos     | 刷卡支付  |
| alipay | scan    | 扫码支付  |
| alipay | transfer    | 帐户转账 |
  
### 2、微信
| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| wechat | mp      | 公众号支付  |
| wechat | miniapp | 小程序支付  |
| wechat | wap     | H5 支付    |
| wechat | scan    | 扫码支付    |
| wechat | pos     | 刷卡支付    |
| wechat | app     | APP 支付  |
| wechat | transfer     | 企业付款  |


## 使用说明 - 支付宝
### 1. 支付订单
```php
use Yansongda\Pay\Pay;

$config = [
    'app_id' => '2016082000295641',
    'notify_url' => 'http://yansongda.cn/alipay_notify.php',
    'return_url' => 'http://yansongda.cn/return.php',
    'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuWJKrQ6SWvS6niI+4vEVZiYfjkCfLQfoFI2nCp9ZLDS42QtiL4Ccyx8scgc3nhVwmVRte8f57TFvGhvJD0upT4O5O/lRxmTjechXAorirVdAODpOu0mFfQV9y/T9o9hHnU+VmO5spoVb3umqpq6D/Pt8p25Yk852/w01VTIczrXC4QlrbOEe3sr1E9auoC7rgYjjCO6lZUIDjX/oBmNXZxhRDrYx4Yf5X7y8FRBFvygIE2FgxV4Yw+SL3QAa2m5MLcbusJpxOml9YVQfP8iSurx41PvvXUMo49JG3BDVernaCYXQCoUJv9fJwbnfZd7J5YByC+5KM4sblJTq7bXZWQIDAQAB',
    'private_key' => 'MIIEpAIBAAKCAQEAs6+F2leOgOrvj9jTeDhb5q46GewOjqLBlGSs/bVL4Z3fMr3p+Q1Tux/6uogeVi/eHd84xvQdfpZ87A1SfoWnEGH5z15yorccxSOwWUI+q8gz51IWqjgZxhWKe31BxNZ+prnQpyeMBtE25fXp5nQZ/pftgePyUUvUZRcAUisswntobDQKbwx28VCXw5XB2A+lvYEvxmMv/QexYjwKK4M54j435TuC3UctZbnuynSPpOmCu45ZhEYXd4YMsGMdZE5/077ZU1aU7wx/gk07PiHImEOCDkzqsFo0Buc/knGcdOiUDvm2hn2y1XvwjyFOThsqCsQYi4JmwZdRa8kvOf57nwIDAQABAoIBAQCw5QCqln4VTrTvcW+msB1ReX57nJgsNfDLbV2dG8mLYQemBa9833DqDK6iynTLNq69y88ylose33o2TVtEccGp8Dqluv6yUAED14G6LexS43KtrXPgugAtsXE253ZDGUNwUggnN1i0MW2RcMqHdQ9ORDWvJUCeZj/AEafgPN8AyiLrZeL07jJz/uaRfAuNqkImCVIarKUX3HBCjl9TpuoMjcMhz/MsOmQ0agtCatO1eoH1sqv5Odvxb1i59c8Hvq/mGEXyRuoiDo05SE6IyXYXr84/Nf2xvVNHNQA6kTckj8shSi+HGM4mO1Y4Pbb7XcnxNkT0Inn6oJMSiy56P+CpAoGBAO1O+5FE1ZuVGuLb48cY+0lHCD+nhSBd66B5FrxgPYCkFOQWR7pWyfNDBlmO3SSooQ8TQXA25blrkDxzOAEGX57EPiipXr/hy5e+WNoukpy09rsO1TMsvC+v0FXLvZ+TIAkqfnYBgaT56ku7yZ8aFGMwdCPL7WJYAwUIcZX8wZ3dAoGBAMHWplAqhe4bfkGOEEpfs6VvEQxCqYMYVyR65K0rI1LiDZn6Ij8fdVtwMjGKFSZZTspmsqnbbuCE/VTyDzF4NpAxdm3cBtZACv1Lpu2Om+aTzhK2PI6WTDVTKAJBYegXaahBCqVbSxieR62IWtmOMjggTtAKWZ1P5LQcRwdkaB2rAoGAWnAPT318Kp7YcDx8whOzMGnxqtCc24jvk2iSUZgb2Dqv+3zCOTF6JUsV0Guxu5bISoZ8GdfSFKf5gBAo97sGFeuUBMsHYPkcLehM1FmLZk1Q+ljcx3P1A/ds3kWXLolTXCrlpvNMBSN5NwOKAyhdPK/qkvnUrfX8sJ5XK2H4J8ECgYAGIZ0HIiE0Y+g9eJnpUFelXvsCEUW9YNK4065SD/BBGedmPHRC3OLgbo8X5A9BNEf6vP7fwpIiRfKhcjqqzOuk6fueA/yvYD04v+Da2MzzoS8+hkcqF3T3pta4I4tORRdRfCUzD80zTSZlRc/h286Y2eTETd+By1onnFFe2X01mwKBgQDaxo4PBcLL2OyVT5DoXiIdTCJ8KNZL9+kV1aiBuOWxnRgkDjPngslzNa1bK+klGgJNYDbQqohKNn1HeFX3mYNfCUpuSnD2Yag53Dd/1DLO+NxzwvTu4D6DCUnMMMBVaF42ig31Bs0jI3JQZVqeeFzSET8fkoFopJf3G6UXlrIEAQ==',
];

$order = [
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject'      => '测试订单-test subject',
];

return Pay::alipay($config)->gateway('web')->pay($order);
```

所有参数均为官方标准参数，无任何差别。[点击这里](https://docs.open.alipay.com/270/alipay.trade.page.pay/ '支付宝电脑支付官方文档') 查看官方文档。

#### 各支付网关说明
- web

- wap

- app

- pos

- scan

- transfer

### 2. 验证回调数据

### 3. 退款

### 4. 关闭订单

### 5. 查询订单


## 使用说明 - 微信
### 1. 支付订单

### 2. 验证回调数据

### 3. 退款

### 4. 关闭订单

### 5. 查询订单


## 代码贡献
由于测试及使用环境的限制，本项目中只开发了「支付宝」和「微信支付」的相关支付网关。

如果您有其它支付网关的需求，或者发现本项目中需要改进的代码，**_欢迎 Fork 并提交 PR！_**


## LICENSE
MIT
