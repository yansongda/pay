# 技术设计：消除 src 内动态实例化

> **状态**：已实施。主体方案经 PR #1194 合并（squash `839321c5`，2026-09-03）；本文档入库与构造函数扁平化（`?? []`）见 PR #1195。
> **关联**：provider 名常量化由 PR #1193 完成；依赖 `yansongda/artful ~1.2.0` + `yansongda/supports ~4.1.0`（PR #1192）。

## 1. 背景与目标

provider 名称已由 PR #1193 全量常量化（src 内 `Pay::PROVIDER_*` 引用 226 处，tests 保持字面量）。实施前 src 内仍剩 2 处 `new $var` 动态实例化：

1. `src/Config.php` 构造函数遍历 `PROVIDER_CONFIG_MAP`（provider → 配置类 class-string），运行时 `new $configClass($config, $tenant)` 转换租户配置对象；
2. `src/Service/AbstractServiceProvider::register()` 取子类 `getProviderClass()` 返回的 class-string 后 `new $class()`。

`new $var` 对 OPcache JIT、phpstan 等静态分析不可解析，AOT 场景优化率为零；而配置类与 Provider 类集合实际完全固定（各 8 个），变量中转没有动态价值。

**目标**：

- src 内动态实例化清零，全部构造调用为字面量；
- 行为零变化：配置转换结果、容器注册双 key、异常消息与 validate 时机完全等价；
- 新增 Provider 仍只需「Config 类 + Provider 类 + ServiceProvider + 注册」四件套，PROVIDERS 名单与 match 为同文件相邻两处。

## 2. 最终方案

### 2.1 Config.php：名单 + match 字面量映射

`PROVIDER_CONFIG_MAP`（provider => class-string）替换为名单常量；构造函数遍历名单，`match ($provider)` 直 new 8 个配置类；`getProviderConfig()` 合法性检查改用 `in_array`：

```php
/**
 * Provider 名单.
 */
private const PROVIDERS = [
    Pay::PROVIDER_WECHAT,
    Pay::PROVIDER_ALIPAY,
    Pay::PROVIDER_AIRWALLEX,
    Pay::PROVIDER_UNIPAY,
    Pay::PROVIDER_JSB,
    Pay::PROVIDER_DOUYIN,
    Pay::PROVIDER_PAYPAL,
    Pay::PROVIDER_STRIPE,
];

public function __construct(array $items = [])
{
    parent::__construct($items);

    // 转换 Provider 配置为对象
    foreach (self::PROVIDERS as $provider) {
        foreach ($this->items[$provider] ?? [] as $tenant => $config) {
            if (is_array($config)) {
                $this->items[$provider][$tenant] = match ($provider) {
                    Pay::PROVIDER_WECHAT => new WechatConfig($config, $tenant),
                    Pay::PROVIDER_ALIPAY => new AlipayConfig($config, $tenant),
                    Pay::PROVIDER_AIRWALLEX => new AirwallexConfig($config, $tenant),
                    Pay::PROVIDER_UNIPAY => new UnipayConfig($config, $tenant),
                    Pay::PROVIDER_JSB => new JsbConfig($config, $tenant),
                    Pay::PROVIDER_DOUYIN => new DouyinConfig($config, $tenant),
                    Pay::PROVIDER_PAYPAL => new PaypalConfig($config, $tenant),
                    Pay::PROVIDER_STRIPE => new StripeConfig($config, $tenant),
                };
            }
        }
    }
}
```

```php
public function getProviderConfig(string $provider, ?string $tenant = null): ProviderConfigInterface
{
    if (!in_array($provider, self::PROVIDERS, true)) {
        throw new InvalidConfigException(Exception::CONFIG_PROVIDER_INVALID, "配置异常: 未知的 Provider - {$provider}");
    }
    // 租户检查、$config->validate() 及返回逻辑不变
}
```

**设计决策**：match 不设 default——provider 已被 `PROVIDERS` 名单约束，名单与 match 失同步时 `UnhandledMatchError` 比静默兜底更显性（属代码缺陷路径，非用户输入路径）。名单与 match 须同改，两者同文件相邻维护。

### 2.2 AbstractServiceProvider：子类字面量实例化

抽象方法 `getProviderClass(): string`（返回 class-string）改为 `makeService(): ProviderInterface`（子类内 new）；注册改用 `$service::class` 作 class-string key：

```php
abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws ContainerException
     */
    public function register(mixed $data = null): void
    {
        $service = $this->makeService();

        Pay::set($service::class, $service);
        Pay::set($this->getProviderName(), $service);
    }

    abstract protected function makeService(): ProviderInterface;

    abstract protected function getProviderName(): string;
}
```

- 8 个子类 `{Provider}ServiceProvider` 统一为 `protected function makeService(): ProviderInterface { return new Xxx(); }`；`getProviderName()` 返回 `Pay::PROVIDER_*` 常量（#1193 已常量化），不动。
- `$service::class` 与原 `$class` 值等价（8 个子类原先均返回字面量 `Xxx::class`），容器双 key（class-string key + 名称 key）的 key 与 value 均不变。
- `ProviderInterface` 为本仓库接口（`Yansongda\Pay\Contract`，pay / query / cancel / close / refund / callback / success 共 7 方法）；`tests/Service/AbstractServiceProviderTest.php` 内嵌 stub 同步实现该接口（tests 唯一改动，其余测试保持字面量）。
- protected 方法 `getProviderClass` → `makeService` 属 internal API 变更，发布时 CHANGELOG 显式标注。

### 2.3 变更总览

| 文件 | 变更 |
| --- | --- |
| `src/Config.php` | map → `PROVIDERS` 名单 + match 字面量映射 + `in_array` 检查 |
| `src/Service/AbstractServiceProvider.php` | `register()` 重写 + `getProviderClass()` → `makeService()` |
| `src/Service/{8}ServiceProvider.php` | `getProviderClass()` → `makeService()` 机械替换，新增 `ProviderInterface` import |
| `tests/Service/AbstractServiceProviderTest.php` | 内嵌 stub 同步 `makeService()` 并 `implements ProviderInterface` |
| `src/Pay.php`、`src/Config/{Xxx}Config.php`、`src/Provider/`、`src/Plugin/`、`src/Traits/`、`web/` | 零改动 |

## 3. 验证门禁（后续合并/重构须保持）

- src 内动态实例化清零：`grep -rn 'new \$' src/` 无输出（`yansongda/artful` 依赖内部不在本仓库）。
- 全局常量精确计数：`grep -rn 'Pay::PROVIDER_' src/ | wc -l` = **234**（#1193 基线 226 + Config 净增 8）。
- 上游常量化未被破坏：
  - `grep -rn "getProviderConfig('" src/` 无输出；
  - `grep -rn "new MethodCalled('\|new CallbackReceived('" src/` 无输出；
  - `grep -rn "return '" src/Service/` 无输出。
- src 魔法字符串清零（`src/Pay.php` 豁免）：`grep -rn "'wechat'\|'alipay'\|'airwallex'\|'unipay'\|'jsb'\|'douyin'\|'paypal'\|'stripe'" src/ | grep -v '^src/Pay.php:'` 无输出。
- use 完整性：凡引用 `Pay::PROVIDER_*` 的文件均持有 `use Yansongda\Pay\Pay;`（`src/Config.php` 与 Pay 同命名空间豁免）。
- `composer cs-fix && composer analyse && composer test` 全绿；全量 **1374 tests / 3286 assertions** 与改前基线一致，无断言修改即通过 = 行为等价的直接证据。

## 4. 范围外与后续注意

- `Pay.php:132-133` → `Artful::load()` 的 `new $service` 动态性位于 `yansongda/artful ~1.2.0` 依赖内部，本仓库不可改，另行处理。
- `tests/` 与 `web/` 文档站的 provider 名保持字符串字面量：前者兼作「常量值 = 真实字符串」的行为锁定，后者为用户面数据。
- **`feat/alipay-v3` 合并注记**：该分支合并将带回净增 11 处魔法字符串（Plugin 层 7 + `src/Provider/Alipay.php` 的 getProviderConfig 3 + CallbackReceived 1），且约 165 个共同文件存在同行冲突（V3 侧字面量 vs master 侧常量）。届时需系统性取 master 侧，再对 V3 独有 11 处按 #1193 同一方式补常量化，防止 §3 门禁随合并回退。
