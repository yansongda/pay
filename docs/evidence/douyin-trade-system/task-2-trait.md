# 2026-09-05 12:02:52

## 处理事项（Todo 2）

- `src/Traits/DouyinTrait.php`：保留 `getDouyinUrl()` 原样，新增 `getDouyinTradeSign()`（C3 交易系统下单签名）与 `verifyDouyinTradeSign()`（C4 回调验签）。异常码复用现有 `CONFIG_DOUYIN_INVALID`/`SIGN_EMPTY`/`SIGN_ERROR`，未新增常量。
- `tests/Traits/DouyinTraitTest.php`：保留 DouyinTraitStub 与原 `testGetDouyinUrl` 用例，新增 11 个用例覆盖精确签名格式断言、应用公钥 round-trip、平台私钥现签回调 round-trip、篡改 body/签名 → SIGN_ERROR、缺任一头及 body 为空 → SIGN_EMPTY、无效私钥/公钥 → InvalidConfigException。

## 命令与真实输出

```
$ docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine sh -c "COMPOSER_ALLOW_SUPERUSER=1 vendor/bin/phpunit tests/Traits/DouyinTraitTest.php"
OK (12 tests, 45 assertions)

$ docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine sh -c "COMPOSER_ALLOW_SUPERUSER=1 composer cs-fix && COMPOSER_ALLOW_SUPERUSER=1 composer analyse && COMPOSER_ALLOW_SUPERUSER=1 composer test"
Found 0 of 470 files that can be fixed
[OK] No errors
OK (1370 tests, 3320 assertions)

$ git status --short tests/TestCase.php
（输出为空，通过）
```

## 验收结果

- Acceptance 1：三绿全过（cs-fix 0 fixable / analyse [OK] / test OK (1370 tests, 3320 assertions)；基线 1359 tests/3281 assertions，差额 = DouyinTraitTest 原 1 个用例替换为 12 个，自洽）。
- Acceptance 2：`tests/Traits/DouyinTraitTest.php` 单跑 12/12 全绿。
- Acceptance 3：`git status --short tests/TestCase.php` 输出为空。

## 偏差

- 机械性修正 1：初版在方法 docblock 写了猜测的抖音官方文档 `@see` URL，因「不猜 API」原则移除（契约快照为唯一依据）。
- 机械性修正 2：cs-fix 要求 use 排序 `Psr\` 在 `Yansongda\` 之前，已修正。
- 测试内部修正：篡改 body 用例的 helper 初版对篡改后 body 签名（自证通过），改为支持 `signBody` 参数对原始 body 签名、发送篡改 body。
- 无设计性偏差；未动 TestCase.php / 未建 GetClientToken 类 / 未实现 getDouyinClientToken（属 Task 4）。
- 备注：git status 中 `M docs/evidence/douyin-trade-system/task-1-cleanup.md`、`?? docs/alipay-v3.md`、`?? docs/douyin-trade-system.md` 为非本任务产生的工作区既有状态，未触碰。

# 2026-09-05 12:08:00 (main agent 亲自验证)

- 单测：`vendor/bin/phpunit tests/Traits/DouyinTraitTest.php` → `OK (12 tests, 45 assertions)`。
- 三绿（容器复跑）：cs-fix `Found 0 of 470` / analyse `[OK] No errors` / test `OK (1370 tests, 3320 assertions)`（1359−1+12=1370 自洽）。
- `git status --short tests/TestCase.php` 输出为空（TestCase 冻结未被触碰）。
- diff 内容级审查：getDouyinTradeSign 待签串 `$method."\n".$uri."\n".$timestamp."\n".$nonce."\n".$body."\n"` 与 C3 精确一致；私钥加载失败与 openssl_sign 失败均抛 InvalidConfigException(CONFIG_DOUYIN_INVALID)；头值 `SHA256-RSA2048 appid="...",nonce_str="...",timestamp="...",key_version=1,signature="..."` 带引号格式与 C3 一致。verifyDouyinTradeSign 头缺失/空 body→SIGN_EMPTY、公钥无效→InvalidConfigException、三行验签串、`1 !== openssl_verify`→SIGN_ERROR，均与 C4/plan 一致。
- 结论：Task 2 通过，勾选 [x]。
