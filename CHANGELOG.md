# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## Unreleased

### Added

- 新增微信小程序虚拟支付支持 (#1172)
  - 新增 `WechatConfigVirtualPay` 配置类（appKey、sandboxAppKey、offerId、encodingAesKey、callbackToken）
  - 新增虚拟支付插件：PayPlugin、CallbackPlugin、AddPayloadSignaturePlugin、VerifySignaturePlugin
  - 新增业务插件：Currency（代币）、Goods（商品）、Order（订单）、Subscribe（订阅）、Withdraw（提现）
  - 新增 `VirtualShortcut` 用于客户端签名场景
  - 新增 `WechatTrait::getWechatVirtualPaySignature()` 和 `getWechatVirtualSessionSignature()` 方法
  - `Wechat::success()` 支持 `['_action' => 'virtual']` 参数返回虚拟支付成功响应
  - 新增 `Wechat::URL_VIRTUAL` 常量（https://api.weixin.qq.com）

### Changed

- 移除所有 Provider 中未使用的 `mergeCommonPlugins` 方法 (#1173)

### Fixed

- 修复 `VirtualShortcut` 插件数组包含非 `PluginInterface` 实现的问题
- 修复虚拟支付测试用例缺少 `access_token` 参数的问题
- 移除测试文件中不必要的 `@internal` 和 `@coversNothing` 注解


## v3.8.0-beta.1 - 2026-05-12

### Fixed

- 微信回调增加时间戳验证防止重放攻击 (#1168)

### Changed

- 更新微信 V3 插件 @see 文档链接地址 (#1167)


## v3.8.0-beta.0 - 2026-05-08

### Added

- 增加 PHP 8.5 支持 (#1139)
- 新增 `Yansongda\Pay\Service\AbstractServiceProvider` 基类 (#1142)
- 新增 `Yansongda\Pay\CertManager` 类用于证书缓存管理 (#1142)
- 新增 Trait 系统替代 Functions.php (#1142, #1143, #1144)
  - `AlipayTrait` - 支付宝相关方法
  - `WechatTrait` - 微信相关方法
  - `UnipayTrait` - 银联相关方法
  - `DouyinTrait` - 抖音相关方法
  - `PaypalTrait` - PayPal 相关方法
  - `JsbTrait` - 江苏银行相关方法
  - `StripeTrait` - Stripe 相关方法
  - `ProviderConfigTrait` - Provider 配置方法
  - `SupportServiceProviderTrait` - ServiceProvider 支持方法
- 新增空中云汇 (Airwallex) 支付支持 (#1140)
- 新增 `AirwallexConfig` 类型化配置 (#1140, #1155)
- 新增 `Yansongda\Pay\Exception\NetworkException` 异常类 (#1157)
- 新增 EdgeCase 边界测试覆盖 (#1157)

### Changed

- 最低 PHP 版本要求从 8.0 升级到 8.2 (#1139)
- `Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\*` 重命名为 `Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\*` (#1139)
- 所有 Plugin 已迁移使用 Trait 方法代替 Functions.php 函数调用 (#1142, #1143, #1144)
- 所有 ServiceProvider 继承 `AbstractServiceProvider` 基类 (#1145)
- 所有 Provider 完成 typed config runtime migration (#1153, #1155)
- 证书逻辑集中到 `CertManager` (#1163)
- 规范化 `CertManager` 方法命名和错误码 (#1160)
- 代码质量优化 - PHPStan 清理、异常扩展、CertManager 重构 (#1157)
- 提取开发规范到 `dev-guide` Skill (#1164)
- 修正 `Config.php` 错误码误用 & `Pay::config()` 去重 (#1162)
- 修复 Trait 静态方法调用的 deprecation warnings (#1147)
- 更新 Scrutinizer 镜像以支持 PHP 8.2 (#1158)
- 升级 dev 依赖到 PHPUnit 11 / Mockery 1.6 / Monolog 3 / Symfony 6.4 并修复 PHPStan 错误
- 更新依赖到最新版本 (#1148)
- 新增 `pr-review-provider` skill 用于 Provider PR 代码审查 (#1149)

### Removed

- `src/Functions.php`，所有辅助函数已迁移到对应的 Trait (#1142, #1143, #1144, #1145)
- `tests/FunctionTest.php`，测试已迁移到 Trait 测试 (#1142, #1143, #1144, #1145)
- `src/Plugin/Wechat/StartPlugin.php`，请使用 `Yansongda\Artful\Plugin\StartPlugin` 代替 (#1139)
- `src/Plugin/Wechat/V3/Marketing/Transfer/` 目录（因微信支付 API 变更）(#1139)
- `get_alipay_config()`、`get_wechat_config()`、`get_unipay_config()` 函数，请使用 `get_provider_config()` 代替 (#1139)
- `WechatConfig` 中未使用的证书相关方法 (#1159)


## v3.7.20

### Added

- 新增 PayPal 支付 (#1127)
- 新增 Stripe 支付 (#1130)

### Fixed

- 修复 localhost 签名验证绕过漏洞 (GHSA-q938-ghwv-8gvc) (#1131)


## v3.7.19

### Added

- 增加支付宝商品文件上传插件 (#1120)


## v3.7.18

### Fixed

- 微信分账参数可能丢失的问题 (#1108)


## v3.7.17

### Fixed

- 事件缺失与不生效的问题 (#1106)


## v3.7.16

### Added

- 新增微信商户转账查询接口 Shortcut (#1099)
- 微信商家转账支持内置异步通知参数（#1100）


## v3.7.15

### Added

- 新增最新版微信商户转账撤销接口（#1096）


## v3.7.14

### Added

- 优化私钥证书的字符串读取方式（#1081）


## v3.7.13

### Added

- 新增支付宝APP同步回调验签 (#1061, #1064)


## v3.7.12

### Added

- 支持最新版微信商户转账 (#1058)


## v3.7.11

### Added

- 新增微信分账申请分账账单插件 (#1041)


## v3.7.10

### Fixed

- 未配置微信证书时，自动获取证书后仍然使用之前的微信配置(#1026)


## v3.7.9

### Added

- 新增抖音支付(#1014)


## v3.7.8

### Added

- 新增 v3 付款码服务商模式(#1010)


## v3.7.7

### Added

- 新增江苏银行e融支付(#1002)


## v3.7.6

### Fixed

- 微信关闭订单报解包错误的问题(#1000, #1001)


## v3.7.5

### Changed

- 优化微信 `ResponsePlugin` 插件去除不必要的返回参数(#996)

### Deprecated

- 微信 `StartPlugin` 改为使用 `yansongda/artful` 中的插件(#993)
- `get_wechat_config`, `get_alipay_config`, `get_unipay_config` 方法已废弃，使用 `get_provider_config` 方法代替(#994)

### Fixed

- 支付宝响应空签名时签名验证逻辑错误的问题(#998)


## v3.7.4

### Changed

- 使用 is_file 代替字符串结尾判断(#982)


## v3.7.3

### Fixed

- 修复商家转账参数缺失的问题(#977)


## v3.7.2

### Added

- 微信V2版本支持普通红包(#973)

### Changed

- 升级 `yansongda/artful` 到最新版解决 http 配置不生效的问题(#974)


## v3.7.1

### Fixed

- 修复微信付款码 shortcut 支付插件执行顺序错误(#972)


## v3.7.0

### Added

- 支持微信 v3 版付款码支付(#969)

### Changed

- 微信付款码支付更改为 v3 版(#969)


## v3.6.5

### Added

- 支付宝根证书配置支持直接配置内容(#959)


## v3.6.4

### Fixed

- 修复支付宝授权访问令牌插件参数问题(#954)


## v3.6.3

### Changed

- 优化微信错误响应时的处理逻辑(#944)


## v3.6.2

### Fixed

- 修复微信 App 支付参数异常问题(#941)


## v3.6.1

### Changed

- 升级 `yansongda/artful` 到 v1.0.9 修复 JsonPacker 为空时 packer 错误的问题(#937)


## v3.6.0

### Added

- 新增 `InvalidSignException`(#903)
- 新增 `DecryptException`(#906)
- 新增 `decrypt_wechat_contents` 解密微信加密内容(#912)
- `\Yansongda\Pay\Plugin\Wechat\Extend\Complaints\QueryDetailPlugin` 自动解密用户手机号(#912)
- 支持 微信/支付宝 多版本(#918)
- 增加 `HttpClientFactoryInterface` 方法用于工厂模式创建 http client(#921)
- 增加银联 `条码支付综合前置平台-被扫支付` 刷卡支付插件(#922)
- 增加小程序虚拟支付签名、用户签名方法(#924)
- 增加微信发票插件(#927)

### Changed

- 查询API方法由 `find` 改为 `query`，同时参数只支持 array(#897)
- cancel/close 的 API 参数只支持 array，不再支持 string(#900, #901)
- 微信合单支付去掉独立的 `combine_app_id`,`combine_mch_id` 配置，复用其它配置(#909)
- 手机网站支付快捷方式由 wap 改为 h5(#911, #915, #916, #934)
- `Pay` 类对外方法由所改变，如果您有自行扩展相关插件，请检查(#926)
- change(internal): 按场景对 支付宝/微信/银联 插件进行分类 && 插件代码优化(#894, #909, #913, #922)
- change(internal): 将 支付/微信/银联 shortcut 从 plugin 文件夹独立出来(#895, #904, #905, #933)
- change(internal): shortcut 完整标明各个插件，不使用 commonPlugin(#886)
- change(internal): DirectionInterface 方法由 `parse` 改为 `guide`(#896)
- change(internal): 错误代码 const 命名规则统一(#902, #903, #906, #909, #926)
- change(internal): 调整 `ProviderInterface` 的返回参数，增加了 `Rocket` 返回(#909)
- change(internal): 将 `call()` 方法重命名为 `shortcut()`(#914)
- change(internal): `mergeCommonPlugins` 不再作为 `AbstractProvider` 的方法(#918)
- change(internal): `AbstractProvider` 默认使用 `HttpClientFactoryInterface` 创建 http client(#921)
- change(internal): 调整 银联 插件文件夹结构(#923)
- change(internal): 替换为 `artful` API 请求框架(#926)
- change(internal): 调整微信代金券插件文件结构(#928)


## v3.5.3

### Changed

- 增加支付宝 分账关系维护/分账查询 插件(#874)
- 支付宝公钥使用公共函数获取(#835)


## v3.5.2

### Fixed

- monolog 不存在时报错问题(#834)
- `\Yansongda\Pay\Provider\AbstractProvider::call` 方法返回值类型错误问题(#834)


## v3.5.1

### Fixed

- `destination` 的类型约束去掉 array(#824)


## v3.5.0

### Removed

- 移除 `Yansongda\Pay\Direction\ArrayDirection` 类(#818, #819)


## v3.4.2

### Changed

- 只支持 hyperf3.x 版本(#815)


## v3.4.1

### Changed

- 优化无签名时错误提示(#813)
- 优化预下单失败时错误提示(#814)


## v3.4.0

### Added

- 增加 `get_direction` 方法获取 `Direction` 对象(#803)

### Changed

- `Exception::INVALID_PARSE` 更改为 `Exception::INVALID_DIRECTION`(#804)
- 最低支持版本变更为 php8.0(#801)
- 优化 coding style 代码规范(#802)


## v3.3.1

### Fixed

- 支付宝沙箱地址(#800)


## v3.3.0

### Added

- 支持微信 v2 版本刷卡支付(#753)
- 增加申请代扣协议插件(#767)
- 增加支付中签约插件(#763)
- 增加只签约插件(#765)
- `shortcut` 支持 `_no_common_plugins` 参数不使用通用插件(#771)
- 增加委托代扣 shortcut(#773)

### Changed

- 重构 ArrayParser 类(#754)
- coding style(#769)
- 优化现有微信v2插件代码(#772)
- 所有参数判断使用 `$payload->has()` 判断是否存在(#778)
- 支持 psr/http-message 2.0 版(#784)
- 所有的 `Find*Plugin` 调整为 `Query*Plugin`(#756)
- 插件开始装载日志由 `info` 调整为 `debug`(#755)
- ParserInterface 签名由 `?ResponseInterface $response` 变更为 `PackerInterface $packer, ?ResponseInterface $response`(#754)
- \Yansongda\Pay\Plugin\Wechat\RadarSignPlugin 增加 `__construct(JsonPacker $jsonPacker, XmlPacker $xmlPacker)` 方法(#753)
- 所有 `Parser` 更名为 `Direction`(#770, #774)
- '_type' 类型统一定义为渠道id，如: 小程序id，公众号id等；增加 '_action' 为操作类型用于 shortcut(#781)
- 默认 container 由 `php-di/php-di` 改为 `hyperf/pimple`(#786)

### Removed

- 移除废弃的类(#752)

### Fixed

- 微信代金券 api 参数错误问题(#777)


## v3.2.14

### Fixed

- 微信投诉相关插件响应解析错误(#746)


## v3.2.13

### Changed

- 微信退款可取消 notify_url(#741)


## v3.2.12

### Added

- 增加获取微信平台公钥证书方法(#733)


## v3.2.11

### Changed

- 增加微信转账注释方便ide识别(#725)


## v3.2.10

### Fixed

- CallbackReceived 事件在获取到回调参数后触发(#716)


## v3.2.9

### Fixed

- 当配置文件出错微信解密失败后报错的问题(#698)


## v3.2.8

### Fixed

- 商家批次单号查询批次单时 query 参数不正确(#690)


## v3.2.7

### Fixed

- 微信批次单号查询批次单时 query 参数不正确(#688)


## v3.2.6

### Fixed

- json 中有 `&` 时解析错误(#687)


## v3.2.5

### Fixed

- 修复支付宝 subject 中存在 + 号回调验签不通过(#684)


## v3.2.4

### Added

- 银联支付(#662)


## v3.2.3

### Added

- 微信 Native 支付支持关联其它类型 appid(#680)


## v3.2.2

### Changed

- 优化支付宝 launch 插件代码(#678)

### Deprecated

- deprecated: 支付宝 `RadarPlugin`, `SignPlugin` 已废弃，使用 `RadarSignPlugin` 代替(#678)
- deprecated: 微信 `SignPlugin` 已废弃，使用 `RadarSignPlugin` 代替(#678)


## v3.2.1

### Changed

- 优化 `wechat_public_cert_path` 配置(#674)

### Fixed

- `wechat_public_cert_path` 未配置时报错的问题(#674)


## v3.2.0

### Changed

- Function 增加命名空间(#665)
- `get_alipay_config`，`get_wechat_config` 返回类型由 `Config` 改为 `array`(#667)
- 支付宝转账查询接口由老版本改为为新版本(#666)
- 支付宝中支付宝根证书、应用证书序列号在常驻进程中缓存(#668)

### Removed

- Function 中将 `get_wechat_authorization` 方法移除(#664)


## v3.1.12

### Changed

- 优化代码 (#661)

### Fixed

- 微信代金券详情 url 不正确(#663)


## v3.1.11

### Added

- 微信退款自动增加回调url(#649)


## v3.1.10

### Added

- 支付宝周期扣款签约接口(#644)


## v3.1.9

### Fixed

- 微信服务商模式预下单存在子商户appid时，invoke 时也应该为子商户 appid (#638)


## v3.1.8

### Fixed

- 提前读取响应数据造成数据错误的问题(#633, #634)


## v3.1.7

### Fixed

- 微信内网页支付供应商模式 sub_appid 非必填(#628)


## v3.1.6

### Fixed

- 微信注释中返回类型错误(#630)


## v3.1.5

### Added

- 微信服务商退款及查询退款支持自动 sub_mchid 参数(#619)


## v3.1.4

### Added

- 支持微信投诉API (#614)


## v3.1.3

### Added

- 配置文件增加第三方应用授权token的支持 (#602)


## v3.1.2

### Fixed

- alipay 中 event dispatch provider 是 wechat 的问题 #595


## v3.1.1

### Fixed

- 设置 container，强制更新 config 后 container 不是设置的 container 的问题 #591


## v3.1.0

### Changed

- 移除 `php-di/php-di` 依赖。如果您使用的框架非 `hyperf`, `laravel` 或 没有指定 `ContainerInterface`，仍需手动安装 `composer require php-di/php-di`
- 移除 `guzzlehttp/guzzle` 依赖。如果没有指定 `\Yansongda\Pay\Contract\HttpClientInterface` 仍需手动安装 `composer require guzzlehttp/guzzle`
- 升级 `yansongda/supports` 到 `~v3.2.0`
- 升级 `php` 最低版本到 `7.4.0`
- 自动识别 `hyperf`, `laravel` 框架，使用相应的 `container` 减少内存占用
- 完全支持 `psr11`，可手动传入 `ContainerInterface` 使用
- `Pay::config(array $config = [], $container = null)` 方法第二个参数增加为 $container，可手动传入 `ContainerInterface`/`Closure`。注意 `Closure` 需最终返回一个 `ContainerInterface` 的实例。

### Fixed

- 解决 php8.1 下 deprecated 的提示


## v3.0.27

### Fixed

- 添加分账接受人姓名加密字段错误 (#566)


## v3.0.26

### Added

- 支持 psr/log 2.x and 3.x (#562)


## v3.0.25

### Fixed

- 支持分账传递姓名 (#559)


## v3.0.24

### Added

- 支持使用小程序等其他类型转账 (#552)


## v3.0.23

### Fixed

- 未设置微信公钥证书时，加密不生效的问题 (#549)


## v3.0.22

### Fixed

- 微信分账传递姓名时未加密的问题 (#547)


## v3.0.21

### Added

- 微信转账快捷方式与加密方式支持 (#542)


## v3.0.20

### Changed

- 完善支付宝响应错误时的异常信息 (#530)


## v3.0.19

### Fixed

- 支付宝 system.oauth.token 请求参数错误 (#528)


## v3.0.18

### Added

- 电商收付通的退款使用 _type 增加多类型 appid (#518)


## v3.0.17

### Added

- 增加电商收付通的退款相关插件 (#513)


## v3.0.16

### Fixed

- app 支付调起签名问题 (#1389476)


## v3.0.15

### Fixed

- 下载对账单时响应解析 (#df27f95)


## v3.0.14

### Fixed

- app 支付调起签名中参数大小写问题 (#7916fdd)


## v3.0.13

### Fixed

- app 支付调起签名中时间戳参数大小写问题 (#510)


## v3.0.12

### Fixed

- 微信小程序支付供应商模式 sub_appid 非必填 (#509)


## v3.0.11

### Added

- 微信 h5 支付支持关联 mini_app_id (#506)


## v3.0.10

### Added

- 服务商批量转账到零钱 (#503)


## v3.0.9

### Added

- 支持直连商户批量转账到零钱 (#501)


## v3.0.8

### Fixed

- 设置 bcscale 时支付宝根证书计算错误的问题 (#492, #494)


## v3.0.7

### Fixed

- 支付宝 wap/web 支付 get 方法时url拼接问题 (#488)


## v3.0.6

### Changed

- 优化服务商模式小程序下单场景 (#487)


## v3.0.5

### Fixed

- 服务商模式交易查询 (#483)


## v3.0.4

### Added

- 支持服务商模式 (#479)
- 支持微信服务商分账功能 (#480)


## v3.0.3

### Added

- 公钥证书增加 cer 后缀支持 (#d22e29a)


## v3.0.2

### Fixed

- 修复微信支付关闭订单时报错问题 (#475)


## v3.0.1

### Fixed

- 修复微信支付关闭订单时报错问题 (#475)
