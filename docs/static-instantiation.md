# 技术设计：消除动态实例化（v3）

> **时间**：2026-09-03
> **作者**：GLM 5.3 Flash + yansongda
> **状态**：已经用户（yansongda）批准（2026-09-03）。演进历史：v1「删常量+全字面量化」→ v2 经用户决策反转为「保留常量+全量常量化」→ v2.1 经 plan-reviewer 初审修订（基线数字/验证门禁/cs-fixer 机制归因/执行改串行）→ **v3（当前）**：上游 PR #1193（ad27a3d3）已吸收常量化全部内容，本设计收敛为剩余的两处动态实例化消除，经用户确认重新收敛。

## 1. 背景与问题

**现状**（master @ ad27a3d3，2026-09-03 已读源码/grep 验证）：

1. `src/Config.php:48`：构造函数（39-53 行）遍历 const `PROVIDER_CONFIG_MAP`（25-34 行，8 个 provider → 配置类 class-string，key 已为 `Pay::PROVIDER_*` 常量），运行时 `new $configClass($config, $tenant)` 转换租户配置对象；`getProviderConfig()`（60-76 行）用该 map 做 provider 合法性检查（62 行）。
2. `src/Service/AbstractServiceProvider.php:19`：`register()`（16-23 行）取子类 `getProviderClass()` 返回的 class-string 后 `new $class()`，再注册双 key；8 个子类（`src/Service/{Xxx}ServiceProvider.php:12-15`）均只返回字面量 `Xxx::class`；`getProviderName()`（17-20 行）已返回 `Pay::PROVIDER_*` 常量且已持有 `use Yansongda\Pay\Pay;`（上游完成）。
3. `new $var` 动态实例化在 src 内**仅剩上述 2 处**（`grep -rn 'new \$' src/` 全仓确认）。
4. provider 名常量化已由上游 PR #1193（2026-09-03 合并）完成：src 内 `Pay::PROVIDER_*` 引用共 226 处（Config 8 + Service 8 + AirwallexTrait 2 + 批量替换 208）；Plugin/Trait 终态为 `use Yansongda\Pay\Pay;` + 短常量；`tests/` 保持字符串字面量（63 处 `getProviderConfig('`）——原 v2 方案的批量常量化部分（v2.1 §3.3）已被上游吸收，实现方式与 v2.1 设计一致。

**困境**：

1. `new $var` 动态实例化对 OPcache JIT、phpstan 等静态工具不可解析，AOT 场景优化率为零；配置类与 Provider 类集合实际完全固定（各 8 个）。
2. `Pay.php:132-133` 经 `Artful::load($provider)` 的动态性位于 `yansongda/artful ~1.2.0` 依赖内，本仓库不可改。

**目标**：

- **行为零变化**：配置对象转换结果、容器注册双 key、异常消息与 validate 时机、事件参数值完全等价
- **全静态实例化**：src 内剩余 2 处 `new $var` 消除，构造调用均为字面量
- **新增 Provider 成本基本不增加**：仍只需 Config 类 + Provider 类 + ServiceProvider + 注册四件套（PROVIDERS 名单与 match 为同文件两处相邻改动）

## 2. 整体方案

核心思路：v2 的两条线中，「provider 名常量化」已由上游完成；本设计只剩一条线——「变量承载类名 → match/子类方法承载**字面量 new**」消除动态实例化。

```
Pay::config($items) ──► new Config($items)
                           │ 旧: foreach PROVIDER_CONFIG_MAP → new $configClass(...)        ← 动态
                           │ 新: foreach PROVIDERS → match($provider) → 8 分支全字面量 new    ← 静态
                           ▼
                       getProviderConfig()  key 检查: isset(MAP[$p]) → in_array(PROVIDERS, $p)

Artful::load() ──► {Provider}ServiceProvider::register()
                       │ 旧: new $this->getProviderClass()                              ← 动态
                       │ 新: $this->makeService() → 子类 return new Wechat()             ← 字面量
                       ▼
                 Pay::set($service::class, ...) + Pay::set($this->getProviderName(), ...)
                 （getProviderName() 上游已返回 Pay::PROVIDER_XXX 常量，不动）
```

**文件结构变更**（无新增/删除文件，不改对外公共 API）：

| 层 | 文件 | 变更 |
|---|---|---|
| 入口 | `src/Pay.php` | 无改动（8 个 `PROVIDER_*` 常量保留） |
| 配置 | `src/Config.php` | const 语义调整（map → 名单，常量构建）+ 构造函数 match + key 检查改写 |
| 服务 | `src/Service/AbstractServiceProvider.php` | `register()` 重写 + `getProviderClass()` → `makeService()` |
| 服务 | `src/Service/{8}ServiceProvider.php` | `getProviderClass()` → `makeService()` 机械替换（`getProviderName()` 不动） |
| 测试 | `tests/Service/AbstractServiceProviderTest.php` | 内嵌 stub 同步（其余测试保持字面量） |

> Plugin/Traits/Provider 三层零改动——上游 PR #1193 已完成常量化，本设计不触碰。

## 3. 详细设计

### 3.1 Config.php：显式 match 映射（常量 key）

```php
// 旧: provider => class-string（值只服务于 new $configClass）
private const PROVIDER_CONFIG_MAP = [Pay::PROVIDER_WECHAT => WechatConfig::class, ...];

// 新: provider 名单（顺序不变），名单与 match key 均为常量引用
private const PROVIDERS = [
    Pay::PROVIDER_WECHAT, Pay::PROVIDER_ALIPAY, Pay::PROVIDER_AIRWALLEX, Pay::PROVIDER_UNIPAY,
    Pay::PROVIDER_JSB, Pay::PROVIDER_DOUYIN, Pay::PROVIDER_PAYPAL, Pay::PROVIDER_STRIPE,
];
```

构造函数伪代码：

```php
foreach self::PROVIDERS as $provider:
    if isset(items[$provider]):
        foreach items[$provider] as tenant => config:
            if is_array(config):
                items[$provider][tenant] = match($provider) {
                    Pay::PROVIDER_WECHAT => new WechatConfig(config, tenant),
                    ...  # 8 分支全字面量 new + 常量 key，顺序与旧 map 一致
                }
```

`getProviderConfig()` 的检查改为 `!in_array($provider, self::PROVIDERS, true)`，异常消息与 `$config->validate()` 不变。

**边界**：`match` 不设 default——provider 已被 `PROVIDERS` 名单约束，名单与 match 失同步时 `UnhandledMatchError` 比静默兜底更显性（属代码缺陷而非用户输入路径）。

### 3.2 AbstractServiceProvider：子类字面量实例化

| 项 | 旧 | 新 |
|---|---|---|
| 抽象方法 1 | `getProviderClass(): string`（返回 class-string） | `makeService(): ProviderInterface`（子类内 new） |
| 抽象方法 2 | `getProviderName(): string` 返回常量（上游已常量化） | **签名与实现均不变，勿动** |
| class-string key | `Pay::set($class, $service)` | `Pay::set($service::class, $service)` |

基类伪代码：

```php
public function register(mixed $data = null): void
{
    $service = $this->makeService();
    Pay::set($service::class, $service);
    Pay::set($this->getProviderName(), $service);
}
```

**关键契约**：

- 8 个子类 `getProviderClass()` 均返回字面量 `Xxx::class`，故 `$service::class` 与旧 `$class` **值等价**——已验证（读过 8 个子类源码，master @ ad27a3d3）。
- 8 个 Provider 类均已 `implements ProviderInterface`——已验证（grep 确认）；接口为**本仓库** `src/Contract/ProviderInterface.php`（命名空间 `Yansongda\Pay\Contract`，7 方法：pay/query/cancel/close/refund/callback/success，签名见 `src/Contract/ProviderInterface.php:19-47`）——已验证（读过源码）；`vendor/yansongda/artful` 中不存在该接口——已验证（grep 零匹配）。
- 基类现状（28 行）已持有 `use Yansongda\Pay\Pay;`（第 9 行），目标形态仅需新增 `use Yansongda\Pay\Contract\ProviderInterface;`——已验证。

**弃用备选**：基类集中 match（违反开闭原则、需 import 8 个 Provider）；删除基类由子类直接实现 `register()`（8 处重复逻辑）。

### 3.3 上游已完成的常量化（存档与门禁）

- PR #1193 按本设计 v2.1 §3.3 的方案实现（FQCN 过渡 → cs-fixer 注入 use → `use Yansongda\Pay\Pay;` + 短常量终态），替换 208 处（基于 f453cce6 基线：R1 177 + R2 20 + R3 11）；tests 字面量保持。本设计不重复实现。
- 原 v2.1 §3.3 的验证 grep（锚定模式清零、全局字面量清零、use 完整性含 `src/Config.php` 豁免）全部移入 plan 的 Final verification，作为「上游常量化未被本设计破坏」的确认门禁。
- **V3 合并注记**（用户决策 2026-09-03）：`feat/alipay-v3` 未来合并会带回净增 **11 处**魔法字符串（实测 454dccec↔bbab5843 diff：Plugin R1 7 处 + `src/Provider/Alipay.php` getProviderConfig 3 处 + Alipay.php CallbackReceived 1 处）。**且合并非平凡**：约 165 个共同文件存在同行冲突（V3 侧字面量 vs master 侧常量，如 `src/Plugin/Unipay/AddRadarPlugin.php`），PaypalTrait/StripeTrait 的 use Pay 行同样冲突；上游 PR #1193 未存档替换脚本与 evidence——届时需系统性取 master 侧后，对 V3 独有 11 处补常量化，防止字面量清零门禁随合并回退。**不在本设计范围**。

### 3.4 范围外

- `Pay.php:132-133` → `Artful::load()` 内部动态性属 `yansongda/artful ~1.2.0`，另行处理。
- `tests/` 与 `web/` 文档站的 provider 名字符串保持字面量——用户面数据；测试字面量兼作「常量值=真实字符串」的行为锁定。
- `feat/alipay-v3` 合并时的新增魔法字符串常量化（见 §3.3 注记）。

## 4. 推进策略

- **阶段 1**：从 master（ad27a3d3）拉新分支（如 `feat/static-instantiation`）单 PR 完成，分 2 个 commit：Config 改造 → Service 改造。**前置**：容器内 `composer update --with-all-dependencies` 对齐依赖（composer.json 要求 artful ~1.2.0/supports ~4.1.0，本地 vendor/lock 实装 1.1.4/4.0.12——已验证脱节）并跑全量测试基线。**验证点**：`composer cs-fix && composer analyse && composer test` 全绿 + `grep -rn 'new \$' src/` 零输出 + 上游常量化门禁保持。**环境前提**：本地无 PHP/composer，全部命令经 container-dev skill 容器执行。
- **阶段 2**：发布时在 CHANGELOG 标注 internal API 变更（protected `getProviderClass` → `makeService`）。
- **回滚**：按 commit 粒度 revert 即可，无数据迁移、无外部状态。

## 5. 风险与对策

| 风险 | 严重度 | 对策 |
|---|---|---|
| `PROVIDERS` 名单与 match 失同步（新增 provider 忘改 match） | 低 | 同文件相邻维护；phpstan + 新增 provider 必加测试的既有流程；文档注明两处同改 |
| `$service::class` 与原 `$class` 值不一致 | 低 | 已验证等价（8 子类均返回字面量）；现有测试断言双 key 一致性，可捕获回归 |
| 外部继承 AbstractServiceProvider 并 override `getProviderClass` 的用户代码破坏 | 低 | protected 属 internal API，CHANGELOG 显式标注 |
| 常量值被误改导致行为漂移 | 低 | tests 以字面量断言真实字符串值（上游保持），常量改动会被测试捕获 |
| 行为差异（异常消息 / validate 时机 / 事件参数值） | 低 | 常量值与字面量恒等；ConfigTest / Provider 测试现有断言把关 |
| Task 2 中间态下任何测试 fatal（`TestCase::setUp` 实例化全部 8 个 ServiceProvider） | 低 | 全程串行（Task 1 → Task 2），见 plan Execution waves 串行理由 |

## 6. 监控与可观测性

不适用（纯代码重构，运行时行为与指标零变化）。
