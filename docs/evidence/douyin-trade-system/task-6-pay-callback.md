# 2026-09-05 12:39:10 (worker-low - Task 6 支付回调验签解析插件)

## 处理事项

新建 2 个文件：
- `src/Plugin/Douyin/V1/Pay/CallbackPlugin.php`：从 `$rocket->getParams()['_request']` 取 `ServerRequestInterface`（缺失/类型不符抛 `InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, ...)`）→ `self::verifyDouyinTradeSign($request, $config)`（DouyinTrait 三行串验签）→ `json_decode` body 后校验顶层 `type === 'payment'`（不匹配抛 9221）→ 取 `msg` 二次 `json_decode`（失败抛 9221）→ pre-$next 模式 `setPayload(new Collection($msg))` + `setDirection(NoHttpRequestDirection::class)` + `setDestination($rocket->getPayload())` + `return $next($rocket)`。配置获取沿用老 master CallbackPlugin 模式：插件 `use DouyinTrait;`（其内嵌 `use ProviderConfigTrait`）+ `self::getProviderConfig(Pay::PROVIDER_DOUYIN, $params)`。
- `tests/Plugin/Douyin/V1/Pay/CallbackPluginTest.php`：4 用例（正常现签解析、篡改签名 SIGN_ERROR、type=refund 9221、缺 _request 9221），签名 helper 沿 DouyinTraitTest 模式用 `tests/Cert/douyinPlatformPrivateKey.pem` 现签 `{timestamp}\n{nonce}\n{body}\n`。

## 命令与真实输出

文件级白名单验收：

```
$ docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine sh -c "COMPOSER_ALLOW_SUPERUSER=1 vendor/bin/phpunit tests/Plugin/Douyin/V1/Pay/CallbackPluginTest.php"

PHPUnit 11.5.56 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.10
Configuration: /app/phpunit.xml

....                                                                4 / 4 (100%)

Time: 00:00.209, Memory: 6.00 MB

OK (4 tests, 15 assertions)
```

初版带 `@internal`/`@coversNothing` doc-comment 时报 1 条 PHPUnit Deprecation（doc-comment 元数据 PHPUnit 12 将弃用），对照仓库惯例（Wechat V3 CallbackPluginTest、Task 3 GetClientTokenPluginTest 均无注解）移除后复跑干净，如上。

git 状态（commit 前）：

```
$ git status --short
 M docs/evidence/douyin-trade-system/task-1-cleanup.md   <- 前序任务遗留，未触碰
 M docs/evidence/douyin-trade-system/task-2-trait.md     <- 前序任务遗留，未触碰
 M docs/evidence/douyin-trade-system/task-3-get-client-token.md <- 前序任务遗留，未触碰
 M docs/evidence/douyin-trade-system/task-4-infra-plugins.md    <- 前序任务遗留，未触碰
?? docs/alipay-v3.md                                     <- 与本任务无关，未触碰
?? docs/douyin-trade-system.md                           <- 与本任务无关，未触碰
?? src/Plugin/Douyin/V1/Pay/CallbackPlugin.php           <- 本任务
?? tests/Plugin/Douyin/V1/Pay/CallbackPluginTest.php     <- 本任务
?? docs/evidence/douyin-trade-system/task-6-pay-callback.md <- 本任务
```

commit（逐文件 add，未用 `git add -A`）：

```
$ git add src/Plugin/Douyin/V1/Pay/CallbackPlugin.php tests/Plugin/Douyin/V1/Pay/CallbackPluginTest.php
$ git add -f docs/evidence/douyin-trade-system/task-6-pay-callback.md
$ git commit -m "feat(douyin): 新增支付回调验签解析插件"
[feature/douyin-trade-system] feat(douyin): 新增支付回调验签解析插件
 3 files changed（本任务 2 个 src/tests 文件 + 本 evidence 文件；具体 commit hash 以 git log 为准，evidence 需先于 commit 成文故不预写 hash）
```

## 验收结果

1. ✅ 文件级白名单 `phpunit tests/Plugin/Douyin/V1/Pay/CallbackPluginTest.php` → `OK (4 tests, 15 assertions)`。
2. ✅ `git status --short` 除本任务 2 个 src/tests 文件与 evidence 外，无本任务引入的其他 src/tests 改动（未触碰 Task 5 的 Sign/Query/QueryCps、Task 7/8 的 Refund/、TestCase.php、DouyinTrait 等）。

## 偏差

1. **机械性修正**：todo 环境事实块写 `use Yansongda\Artful\Plugin\NoHttpRequestDirection;`，但 vendor 实际路径为 `Yansongda\Artful\Direction\NoHttpRequestDirection`（老 master CallbackPlugin 同款 import）。按实际路径写，属环境事实笔误，不改设计。
2. 无设计性偏差。
