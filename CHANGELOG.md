## 3.7.7

### added

- feat: 新增江苏银行e融支付(#1002)

## v3.7.6

### fixed

- fix: 微信关闭订单报解包错误的问题(#1000, #1001)

## v3.7.5

### fixed

- fix: 支付宝响应空签名时签名验证逻辑错误的问题(#998)

### optimized

- optimize: 优化微信 `ResponsePlugin` 插件去除不必要的返回参数(#996)

### deprecated

- deprecate: 微信 `StartPlugin` 改为使用 `yansongda/artful` 中的插件(#993)
- deprecate: `get_wechat_config`, `get_alipay_config`, `get_unipay_config` 方法已废弃，使用 `get_provider_config` 方法代替(#994)

## v3.7.4

### optimized

- optimize: 使用 is_file 代替字符串结尾判断(#982)

## v3.7.3

### fixed

- fix: 修复商家转账参数缺失的问题(#977)

## v3.7.2

### added

- feat: 微信V2版本支持普通红包(#973)

### chore

- chore: 升级 `yansongda/artful` 到最新版解决 http 配置不生效的问题(#974)

## v3.7.1

### fixed

- fix: 修复微信付款码 shortcut 支付插件执行顺序错误(#972)

## v3.7.0

### added

- feat: 支持微信 v3 版付款码支付(#969)

### changed

- changed: 微信付款码支付更改为 v3 版(#969)

## v3.6.5

### added

- feat: 支付宝根证书配置支持直接配置内容(#959)

## v3.6.4

### fixed

- fix: 修复支付宝授权访问令牌插件参数问题(#954)

## v3.6.3

### optimized

- optimize: 优化微信错误响应时的处理逻辑(#944)

## v3.6.2

### fixed

- fix: 修复微信 App 支付参数异常问题(#941)

## v3.6.1

### chore

- chore: 升级 `yansongda/artful` 到 v1.0.9 修复 JsonPacker 为空时 packer 错误的问题(#937)

## v3.6.0

### added

- feat: 新增 `InvalidSignException`(#903)
- feat: 新增 `DecryptException`(#906)
- feat: 新增 `decrypt_wechat_contents` 解密微信加密内容(#912)
- feat: `\Yansongda\Pay\Plugin\Wechat\Extend\Complaints\QueryDetailPlugin` 自动解密用户手机号(#912)
- feat: 支持 微信/支付宝 多版本(#918)
- feat: 增加 `HttpClientFactoryInterface` 方法用于工厂模式创建 http client(#921)
- feat: 增加银联 `条码支付综合前置平台-被扫支付` 刷卡支付插件(#922)
- feat: 增加小程序虚拟支付签名、用户签名方法(#924)
- feat: 增加微信发票插件(#927)

### changed

- change: 查询API方法由 `find` 改为 `query`，同时参数只支持 array(#897)
- change: cancel/close 的 API 参数只支持 array，不再支持 string(#900, #901)
- change: 微信合单支付去掉独立的 `combine_app_id`,`combine_mch_id` 配置，复用其它配置(#909)
- change: 手机网站支付快捷方式由 wap 改为 h5(#911, #915, #916, #934)
- change: `Pay` 类对外方法由所改变，如果您有自行扩展相关插件，请检查(#926)
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

### feat

- feat: 增加支付宝 分账关系维护/分账查询 插件(#874)

### optimized

- optimize: 支付宝公钥使用公共函数获取(#835)

## v3.5.2

### fixed

- fix: monolog 不存在时报错问题(#834)
- fix: `\Yansongda\Pay\Provider\AbstractProvider::call` 方法返回值类型错误问题(#834)

## v3.5.1

### fixed

- fix: `destination` 的类型约束去掉 array(#824)

## v3.5.0

### deleted

- deleted: 移除 `Yansongda\Pay\Direction\ArrayDirection` 类(#818, #819)

## v3.4.2

### changed

- change: 只支持 hyperf3.x 版本(#815)

## v3.4.1

### optimized

- optimize: 优化无签名时错误提示(#813)
- optimize: 优化预下单失败时错误提示(#814)

## v3.4.0

### added

- feat: 增加 `get_direction` 方法获取 `Direction` 对象(#803)

### changed

- change: `Exception::INVALID_PARSE` 更改为 `Exception::INVALID_DIRECTION`(#804)
- chore: 最低支持版本变更为 php8.0(#801)

### optimized

- optimize: 优化 coding style 代码规范(#802)

## v3.3.1

### fixed

- fix: 支付宝沙箱地址(#800)

## v3.3.0

### added

- feat: 支持微信 v2 版本刷卡支付(#753)
- feat: 增加申请代扣协议插件(#767)
- feat: 增加支付中签约插件(#763)
- feat: 增加只签约插件(#765)
- feat: `shortcut` 支持 `_no_common_plugins` 参数不使用通用插件(#771)
- feat: 增加委托代扣 shortcut(#773)

### deleted

- delete: 移除废弃的类(#752)

### fixed

- fix: 微信代金券 api 参数错误问题(#777)

### refactor

- refactor: 重构 ArrayParser 类(#754)
- refactor: coding style(#769)
- refactor: 优化现有微信v2插件代码(#772)
- refactor: 所有参数判断使用 `$payload->has()` 判断是否存在(#778)

### chore

- chore: 支持 psr/http-message 2.0 版(#784)

### changed

- change: 所有的 `Find*Plugin` 调整为 `Query*Plugin`(#756)
- change: 插件开始装载日志由 `info` 调整为 `debug`(#755)
- change: ParserInterface 签名由 `?ResponseInterface $response` 变更为 `PackerInterface $packer, ?ResponseInterface $response`(#754)
- change: \Yansongda\Pay\Plugin\Wechat\RadarSignPlugin 增加 `__construct(JsonPacker $jsonPacker, XmlPacker $xmlPacker)` 方法(#753)
- change: 所有 `Parser` 更名为 `Direction`(#770, #774)
- change: '_type' 类型统一定义为渠道id，如: 小程序id，公众号id等；增加 '_action' 为操作类型用于 shortcut(#781)
- change: 默认 container 由 `php-di/php-di` 改为 `hyperf/pimple`(#786)

## v3.2.14

### fixed

- fix: 微信投诉相关插件响应解析错误(#746)

## v3.2.13

### optimized

- optimize: 微信退款可取消 notify_url(#741)

## v3.2.12

### added

- feat: 增加获取微信平台公钥证书方法(#733)

## v3.2.11

### docs

- docs: 增加微信转账注释方便ide识别(#725)

## v3.2.10

### fixed

- fix: CallbackReceived 事件在获取到回调参数后触发(#716)

## v3.2.9

### fixed

- fix: 当配置文件出错微信解密失败后报错的问题(#698)

## v3.2.8

### fixed

- fix: 商家批次单号查询批次单时 query 参数不正确(#690)

## v3.2.7

### fixed

- fix: 微信批次单号查询批次单时 query 参数不正确(#688)

## v3.2.6

### fixed

- fix: json 中有 `&` 时解析错误(#687)

## v3.2.5

### fixed

- fix: 修复支付宝 subject 中存在 + 号回调验签不通过(#684)

## v3.2.4

### added

- feat: 银联支付(#662)

## v3.2.3

### added

- feat: 微信 Native 支付支持关联其它类型 appid(#680)

## v3.2.2

### refactor

- refactor: 优化支付宝 launch 插件代码(#678)

### deprecated

- deprecated: 支付宝 `RadarPlugin`, `SignPlugin` 已废弃，使用 `RadarSignPlugin` 代替(#678)
- deprecated: 微信 `SignPlugin` 已废弃，使用 `RadarSignPlugin` 代替(#678)

## v3.2.1

### fixed

- fix: `wechat_public_cert_path` 未配置时报错的问题(#674)

### refactor

- refactor: 优化 `wechat_public_cert_path` 配置(#674)

## v3.2.0

### changed

- change: Function 增加命名空间(#665)
- change: `get_alipay_config`，`get_wechat_config` 返回类型由 `Config` 改为 `array`(#667)
- change: 支付宝转账查询接口由老版本改为为新版本(#666)

### deleted

- delete: Function 中将 `get_wechat_authorization` 方法移除(#664)

### performance

- perf: 支付宝中支付宝根证书、应用证书序列号在常驻进程中缓存(#668)

## v3.1.12

### fixed

- fix: 微信代金券详情 url 不正确(#663)

### refactor

- refactor: 优化代码 (#661)

## v3.1.11

### added

- feat: 微信退款自动增加回调url(#649)

## v3.1.10

### added

- feat: 支付宝周期扣款签约接口(#644)

## v3.1.9

### fixed

- fix: 微信服务商模式预下单存在子商户appid时，invoke 时也应该为子商户 appid (#638)

## v3.1.8

### fixed

- fix: 提前读取响应数据造成数据错误的问题(#633, #634)

## v3.1.7

### fixed

- fix: 微信内网页支付供应商模式 sub_appid 非必填(#628)

## v3.1.6

### fixed

- fix: 微信注释中返回类型错误(#630)

## v3.1.5

### added

- feat: 微信服务商退款及查询退款支持自动 sub_mchid 参数(#619)

## v3.1.4

### added

- feat: 支持微信投诉API (#614)

## v3.1.3

### added

- feat: 配置文件增加第三方应用授权token的支持 (#602)

## v3.1.2

### fixed

- fix: alipay 中 event dispatch provider 是 wechat 的问题 #595

## v3.1.1

### fixed

- fix: 设置 container，强制更新 config 后 container 不是设置的 container 的问题 #591

## v3.1.0

兼容 v3.0 版本，推荐升级(#579)

### dependency

- delete: 移除 `php-di/php-di` 依赖。如果您使用的框架非 `hyperf`, `laravel` 或 没有指定 `ContainerInterface`，仍需手动安装 `composer require php-di/php-di`
- delete: 移除 `guzzlehttp/guzzle` 依赖。如果没有指定 `\Yansongda\Pay\Contract\HttpClientInterface` 仍需手动安装 `composer require guzzlehttp/guzzle`
- upgrade: 升级 `yansongda/supports` 到 `~v3.2.0`
- upgrade: 升级 `php` 最低版本到 `7.4.0`

### fixed

- fix: 解决 php8.1 下 deprecated 的提示

### kernel

- refactor: 自动识别 `hyperf`, `laravel` 框架，使用相应的 `container` 减少内存占用
- refactor: 完全支持 `psr11`，可手动传入 `ContainerInterface` 使用
- changed: `Pay::config(array $config = [], $container = null)` 方法第二个参数增加为 $container，可手动传入 `ContainerInterface`/`Closure`。注意 `Closure` 需最终返回一个 `ContainerInterface` 的实例。

## v3.0.27

### fixed

- fix: 添加分账接受人姓名加密字段错误 (#566)

## v3.0.26

### added

- feat: 支持 psr/log 2.x and 3.x (#562)

## v3.0.25

### fixed

- fix: 支持分账传递姓名 (#559)

## v3.0.24

### added

- feat: 支持使用小程序等其他类型转账 (#552)

## v3.0.23

### fixed

- fix: 未设置微信公钥证书时，加密不生效的问题 (#549)

## v3.0.22

### fixed

- fix: 微信分账传递姓名时未加密的问题 (#547)

## v3.0.21

### added

- feat: 微信转账快捷方式与加密方式支持 (#542)

## v3.0.20

### updated

- chore: 完善支付宝响应错误时的异常信息 (#530)

## v3.0.19

### fixed

- fix: 支付宝 system.oauth.token 请求参数错误 (#528)

## v3.0.18

### added

- feat: 电商收付通的退款使用 _type 增加多类型 appid (#518)

## v3.0.17

### added

- feat: 增加电商收付通的退款相关插件 (#513)

## v3.0.16

### fixed

- fixed: app 支付调起签名问题 (#1389476)

## v3.0.15

### fixed

- fixed: 下载对账单时响应解析 (#df27f95)

## v3.0.14

### fixed

- fixed: app 支付调起签名中参数大小写问题 (#7916fdd)

## v3.0.13

### fixed

- fixed: app 支付调起签名中时间戳参数大小写问题 (#510)

## v3.0.12

### fixed

- fixed: 微信小程序支付供应商模式 sub_appid 非必填 (#509)

## v3.0.11

### added

- feat: 微信 h5 支付支持关联 mini_app_id (#506)

## v3.0.10

### added

- feat: 服务商批量转账到零钱 (#503)

## v3.0.9

### added

- feat: 支持直连商户批量转账到零钱 (#501)

## v3.0.8

### fixed

- fix: 设置 bcscale 时支付宝根证书计算错误的问题 (#492, #494)

## v3.0.7

### fixed

- fix: 支付宝 wap/web 支付 get 方法时url拼接问题 (#488)

## v3.0.6

### optimized

- chore: 优化服务商模式小程序下单场景 (#487)

## v3.0.5

### fixed

- fix: 服务商模式交易查询 (#483)

## v3.0.4

### added

- feat: 支持服务商模式 (#479)
- feat: 支持微信服务商分账功能 (#480)

## v3.0.3

### added

- feat: 公钥证书增加 cer 后缀支持 (#d22e29a)

## v3.0.2

### fixed

- 修复微信支付关闭订单时报错问题 (#475)

## v3.0.1

### fixed

- 修复微信支付关闭订单时报错问题 (#475)
