# 安装

## 运行环境

- PHP 7.3+ (v3.1.0 开始需 7.4+)
- composer

## 安装总结

对于绝大多数用户而言，您只需要记住以下两个原则即可：

### hyperf/laravel 用户

```shell
composer require yansongda/pay:~3.2.0 -vvv
composer require guzzlehttp/guzzle:^7.0 # 默认情况下，此包框架已自带，无需额外安装
```

### 其它框架/无框架 用户

```shell
composer require yansongda/pay:~3.2.0 -vvv
composer require guzzlehttp/guzzle:^7.0
composer require php-di/php-di -W
```

## 详细安装介绍

```shell
composer require yansongda/pay:~3.2.0 -vvv
```

由于 `yansongda/pay` 支持 PSR2、PSR3、PSR4、PSR7、PSR11、PSR14、PSR18 等各项标准，因此这里额外介绍下 PSR-11、PSR-18 的安装与使用。

### 关于 Container（PSR-11）

::: tip
如果您看不懂这部分内容:

1、hyperf/laravel 用户直接忽略此部分内容；

2、其它用户（包括 thinkphp 用户）在安装完 `Pay` 后直接无脑 `composer require php-di/php-di` 即可
:::

#### hyperf/laravel 用户

`Pay` 会自动复用框架内的 Container, 无需您任何额外操作。

#### 其它框架/无框架 用户

如果您不想操心那么多，SDK 自带了一套开箱即用的 Container，但仍然需要手动安装 php-di:

```shell
composer require php-di/php-di
```


如果您所使用的框架内有符合 `PSR-11` 的 `Container`，您需要在初始化 **之前**（即，调用 `Pay::config()` 方法之前）执行以下代码即可复用现有的 `Container`:

```php
use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;

// $container = 您现有的 container

// 方法一：
Pay::setContainer($container);
Pay::config($config);

// 方法二：
Pay::config($config, function () use ($container) {
    return $container;
});
```

### 关于 Guzzlehttp (PSR-18)

::: tip
如果您看不懂这部分内容:

在安装完 `Pay` 后直接无脑 `composer require guzzlehttp/guzzle:^7.0` 即可
:::

#### 使用默认的 Client

SDK 自带了一套开箱即用的 HTTP 客户端，但仍然需要手动安装 Guzzlehttp:

```shell
composer require guzzlehttp/guzzle:^7.0
```

#### 现有框架有 PSR-18 的 Client

如果您所使用的框架内有符合 `PSR-18` 的 `HTTP Client`，您需要在初始化 **之后**（即，调用 `Pay::config()` 方法后）执行以下代码即可复用现有的 `Client`:

```php
use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;

// $client = 您现有的 http client

Pay::config($config);
Pay::set(HttpClientInterface::class, $client);
```
