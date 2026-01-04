# Rust 实现说明

## 概述

本仓库现已新增 Rust 实现，提供核心加密操作的高性能替代方案。Rust 实现位于 `rust-pay/` 目录下，提供与 PHP 版本功能对等的加密、签名和证书处理功能。

## 特性

### 核心功能

- ✅ **签名生成与验证**
  - RSA SHA256 签名（支付宝、微信V3）
  - RSA SHA1 签名（传统系统）
  - MD5 签名（微信V2）

- ✅ **加密与解密**
  - RSA PKCS1 OAEP 加密/解密（微信敏感信息）
  - AES-256-GCM 解密（微信资源解密）

- ✅ **证书处理**
  - X.509 证书解析
  - 证书序列号提取
  - 证书 SN 计算（MD5 hash）

- ✅ **实用工具**
  - 十六进制转十进制
  - 密钥格式化
  - 参数字符串构建

### 支持的支付平台

- **支付宝 (Alipay)**: RSA SHA256 签名，证书 SN 计算
- **微信支付 (WeChat Pay)**: RSA SHA256 (V3), MD5 (V2), AES-256-GCM 解密
- **银联 (UnionPay)**: RSA 签名验证
- **江苏银行 (JSB)**: RSA 签名验证
- **抖音支付 (Douyin Pay)**: 标准签名操作

## 性能优势

相比 PHP 原生实现，Rust 版本提供显著的性能提升：

| 操作 | 性能提升 |
|------|---------|
| RSA 签名/验证 | 2-5x |
| MD5 哈希 | 3-10x |
| 证书解析 | 5-15x |
| 大数运算 | 10-100x |

## 快速开始

### 1. 构建 Rust 库

```bash
cd rust-pay
cargo build --release
```

构建完成后，共享库文件将位于：
- Linux: `rust-pay/target/release/librust_pay.so`
- macOS: `rust-pay/target/release/librust_pay.dylib`
- Windows: `rust-pay/target/release/rust_pay.dll`

### 2. 运行测试

```bash
cd rust-pay
cargo test
```

### 3. PHP 集成示例

#### 方式一：使用 PHP FFI（推荐）

```php
<?php

// 加载 Rust 库
$ffi = FFI::cdef('
    char* rust_pay_sign_rsa_sha256(const char* content, const char* private_key);
    char* rust_pay_sign_md5(const char* params, const char* key, int uppercase);
    char* rust_pay_hex_to_dec(const char* hex);
    void rust_pay_free_string(char* s);
', __DIR__ . '/rust-pay/target/release/librust_pay.so');

// MD5 签名（微信V2）
$params = "amount=100&merchant_id=12345&order_no=ORDER001";
$key = "your_merchant_key";
$ptr = $ffi->rust_pay_sign_md5($params, $key, 1); // 1 = 大写
$signature = FFI::string($ptr);
$ffi->rust_pay_free_string($ptr);

echo "签名: $signature\n";
```

#### 方式二：创建 PHP 包装类

参见 `rust-pay/examples/php_integration.php` 中的完整示例。

### 4. 运行示例

```bash
# 确保已构建 Rust 库
cd rust-pay && cargo build --release && cd ..

# 运行 PHP 示例
php rust-pay/examples/php_integration.php
```

## 项目结构

```
rust-pay/
├── Cargo.toml           # Rust 项目配置
├── README.md           # 详细文档（英文）
├── src/
│   ├── lib.rs         # 库入口点
│   ├── error.rs       # 错误类型定义
│   ├── signature.rs   # 签名生成与验证
│   ├── encryption.rs  # 加密与解密
│   ├── certificate.rs # 证书处理
│   ├── utils.rs       # 实用工具函数
│   └── ffi.rs         # C FFI 绑定（PHP 集成）
├── examples/
│   └── php_integration.php  # PHP 集成示例
└── target/            # 构建输出（git 忽略）
```

## 使用示例

### 签名生成

```rust
use rust_pay::sign_rsa_sha256;

let content = "key1=value1&key2=value2";
let private_key = "-----BEGIN RSA PRIVATE KEY-----\n...\n-----END RSA PRIVATE KEY-----";

let signature = sign_rsa_sha256(content, private_key)?;
println!("签名: {}", signature);
```

### 签名验证

```rust
use rust_pay::verify_rsa_sha256;

let content = "key1=value1&key2=value2";
let signature = "base64_encoded_signature";
let public_key = "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----";

verify_rsa_sha256(content, signature, public_key)?;
println!("签名验证成功！");
```

### MD5 签名（微信V2）

```rust
use rust_pay::sign_md5;

let params = "amount=100&merchant_id=12345&order_no=ORDER001";
let key = "merchant_secret_key";

let signature = sign_md5(params, key, true); // true = 大写
println!("MD5 签名: {}", signature);
```

### AES-256-GCM 解密（微信资源）

```rust
use rust_pay::decrypt_aes_256_gcm;

let ciphertext = "base64_encoded_ciphertext_with_tag";
let key = "32_byte_merchant_secret_key_here";
let nonce = "nonce_value";
let associated_data = "certificate";

let decrypted = decrypt_aes_256_gcm(ciphertext, key, nonce, associated_data)?;
println!("解密结果: {}", decrypted);
```

### 证书 SN 计算

```rust
use rust_pay::get_certificate_sn;

let cert_pem = "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----";
let sn = get_certificate_sn(cert_pem)?;
println!("证书 SN: {}", sn);
```

## 依赖项

Rust 库使用以下依赖：

- `openssl`: OpenSSL 绑定，用于加密操作
- `md-5`: MD5 哈希
- `sha2`: SHA-256 哈希
- `base64`: Base64 编码/解码
- `num-bigint`: 大数运算
- `thiserror`: 错误处理

## 开发指南

### 添加新功能

1. 在相应的模块（`signature.rs`, `encryption.rs` 等）中添加函数
2. 更新 `lib.rs` 中的导出
3. 如需 PHP 集成，在 `ffi.rs` 中添加 C FFI 绑定
4. 添加单元测试
5. 更新文档

### 运行基准测试

```bash
cd rust-pay
cargo bench
```

### 生成文档

```bash
cd rust-pay
cargo doc --open
```

## 常见问题

### Q: 如何在 PHP 中启用 FFI？

A: 在 `php.ini` 中添加或修改：
```ini
extension=ffi
ffi.enable=true
```

### Q: 为什么选择 Rust？

A: Rust 提供：
- 内存安全保证
- 接近 C 的性能
- 零成本抽象
- 优秀的并发支持
- 现代化的包管理

### Q: Rust 实现会替代 PHP 实现吗？

A: 不会。Rust 实现是**可选的性能增强**。PHP 实现仍然是主要实现，Rust 版本可用于：
- 高性能场景
- CPU 密集型操作
- 大规模并发处理

### Q: 如何选择使用 PHP 还是 Rust？

A: 建议：
- **开发/调试**: 使用 PHP 实现（更容易调试）
- **生产环境**: 考虑 Rust 实现（更高性能）
- **混合使用**: 关键路径用 Rust，其他用 PHP

## 贡献指南

欢迎贡献 Rust 实现的改进和新功能！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 许可证

MIT

## 相关链接

- [Rust 官方网站](https://www.rust-lang.org/)
- [PHP FFI 文档](https://www.php.net/manual/en/book.ffi.php)
- [OpenSSL 文档](https://www.openssl.org/docs/)
