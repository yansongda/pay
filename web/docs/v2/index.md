# 快速入门

该文档为 v2.x 版本，如果您想找 v1.x 版本文档，请点击[https://github.com/yansongda/pay/tree/v1](https://github.com/yansongda/pay/tree/v1)

**注意：v1.x 与 v2.x 版本不兼容**

开发了多次支付宝与微信支付后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

**！！请先熟悉 支付宝/微信 说明文档！！请具有基本的 debug 能力！！**

欢迎 Star，欢迎 PR！

laravel 扩展包请 [https://github.com/yansongda/laravel-pay](https://github.com/yansongda/laravel-pay)

QQ交流群：690027516

---


## 特点
- 丰富的事件系统
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 符合 PSR 标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的


## 支持的支付方法
### 1、支付宝
- 电脑支付
- 手机网站支付
- APP 支付
- 刷卡支付
- 扫码支付
- 账户转账
- 小程序支付

|  method   |   描述       |
| :-------: | :-------:   |
|  web      | 电脑支付     |
|  wap      | 手机网站支付 |
|  app      | APP 支付    |
|  pos      | 刷卡支付  |
|  scan     | 扫码支付  |
|  transfer | 帐户转账  |
|  mini     | 小程序支付 |

### 2、微信
- 公众号支付
- 小程序支付
- H5 支付
- 扫码支付
- 刷卡支付
- APP 支付
- 企业付款
- 普通红包
- 分裂红包

| method |   描述     |
| :-----: | :-------: |
| mp      | 公众号支付  |
| miniapp | 小程序支付  |
| wap     | H5 支付    |
| scan    | 扫码支付    |
| pos     | 刷卡支付    |
| app     | APP 支付  |
| transfer     | 企业付款 |
| redpack      | 普通红包 |
| groupRedpack | 分裂红包 |


## 支持的方法
所有网关均支持以下方法

- find(array/string $order)  
说明：查找订单接口  
参数：`$order` 为 `string` 类型时，请传入系统订单号，对应支付宝或微信中的 `out_trade_no`； `array` 类型时，参数请参考支付宝或微信官方文档。  
返回：查询成功，返回 `Yansongda\Supports\Collection` 实例，可以通过 `$colletion->xxx` 或 `$collection['xxx']` 访问服务器返回的数据。  
异常：`GatewayException` 或 `InvalidSignException`  

- refund(array $order)  
说明：退款接口  
参数：`$order` 数组格式，退款参数。  
返回：退款成功，返回 `Yansongda\Supports\Collection` 实例，可以通过 `$colletion->xxx` 或 `$collection['xxx']` 访问服务器返回的数据。  
异常：`GatewayException` 或 `InvalidSignException`

- cancel(array/string $order)  
说明：取消订单接口  
参数：`$order` 为 `string` 类型时，请传入系统订单号，对应支付宝或微信中的 `out_trade_no`； `array` 类型时，参数请参考支付宝或微信官方文档。    
返回：取消成功，返回 `Yansongda\Supports\Collection` 实例，可以通过 `$colletion->xxx` 或 `$collection['xxx']` 访问服务器返回的数据。  
异常：`GatewayException` 或 `InvalidSignException`

- close(array/string $order)  
说明：关闭订单接口  
参数：`$order` 为 `string` 类型时，请传入系统订单号，对应支付宝或微信中的 `out_trade_no`； `array` 类型时，参数请参考支付宝或微信官方文档。  
返回：关闭成功，返回 `Yansongda\Supports\Collection` 实例，可以通过 `$colletion->xxx` 或 `$collection['xxx']` 访问服务器返回的数据。  
异常：`GatewayException` 或 `InvalidSignException`  

- verify()  
说明：验证服务器返回消息是否合法  
返回：验证成功，返回 `Yansongda\Supports\Collection` 实例，可以通过 `$colletion->xxx` 或 `$collection['xxx']` 访问服务器返回的数据。  
异常：`GatewayException` 或 `InvalidSignException`  

- PAYMETHOD(array $order)  
说明：进行支付；具体支付方法名称请参考「支持的支付方法」一栏  
返回：成功，返回 `Yansongda\Supports\Collection` 实例，可以通过 `$colletion->xxx` 或 `$collection['xxx']` 访问服务器返回的数据或 `Symfony\Component\HttpFoundation\Response` 实例，可通过 `return $response->send()`(laravel 框架中直接 `return $response`) 返回，具体请参考文档。  
异常：`GatewayException` 或 `InvalidSignException`  


## 错误

如果在调用相关支付网关 API 时有错误产生，会抛出 `GatewayException`,`InvalidSignException` 错误，可以通过 `$e->getMessage()` 查看，同时，也可通过 `$e->raw` 查看调用 API 后返回的原始数据，该值为数组格式。


## 所有异常

* Yansongda\Pay\Exceptions\InvalidGatewayException ，表示使用了除本 SDK 支持的支付网关。
* Yansongda\Pay\Exceptions\InvalidSignException ，表示验签失败。
* Yansongda\Pay\Exceptions\InvalidConfigException ，表示缺少配置参数，如，`ali_public_key`, `private_key` 等。
* Yansongda\Pay\Exceptions\GatewayException ，表示支付宝/微信服务器返回的数据非正常结果，例如，参数错误，对账单不存在等。
