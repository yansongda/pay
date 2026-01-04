<?php
/**
 * PHP FFI Integration Example for Rust Pay Library
 * 
 * This example demonstrates how to call Rust functions from PHP using FFI.
 * 
 * Requirements:
 * - PHP 7.4+ with FFI extension enabled
 * - Compiled Rust library (rust-pay/target/release/librust_pay.so on Linux)
 * 
 * Usage:
 *   php rust-pay/examples/php_integration.php
 */

// Check if FFI is available
if (!extension_loaded('ffi')) {
    die("Error: FFI extension is not loaded. Enable it in php.ini\n");
}

// Path to the compiled Rust library
$libPath = __DIR__ . '/../target/release/librust_pay.so';

// On macOS, use .dylib instead
if (PHP_OS === 'Darwin') {
    $libPath = __DIR__ . '/../target/release/librust_pay.dylib';
}

// On Windows, use .dll
if (PHP_OS_FAMILY === 'Windows') {
    $libPath = __DIR__ . '/../target/release/rust_pay.dll';
}

if (!file_exists($libPath)) {
    die("Error: Rust library not found at: $libPath\n");
    die("Please build the library first: cd rust-pay && cargo build --release\n");
}

// Define FFI bindings
$ffi = FFI::cdef('
    char* rust_pay_sign_rsa_sha256(const char* content, const char* private_key);
    int rust_pay_verify_rsa_sha256(const char* content, const char* signature, const char* public_key);
    char* rust_pay_sign_md5(const char* params, const char* key, int uppercase);
    char* rust_pay_decrypt_aes_256_gcm(const char* ciphertext, const char* key, const char* nonce, const char* aad);
    char* rust_pay_get_certificate_sn(const char* cert_pem);
    char* rust_pay_hex_to_dec(const char* hex);
    void rust_pay_free_string(char* s);
', $libPath);

/**
 * Helper function to get string from C pointer and free it
 */
function getStringAndFree($ffi, $ptr): ?string
{
    if ($ptr === null || FFI::isNull($ptr)) {
        return null;
    }
    
    $str = FFI::string($ptr);
    $ffi->rust_pay_free_string($ptr);
    
    return $str;
}

echo "=== Rust Pay Library Integration Demo ===\n\n";

// Example 1: MD5 Signature (WeChat V2 style)
echo "1. MD5 Signature Generation\n";
$params = "amount=100&merchant_id=12345&order_no=ORDER001";
$key = "test_merchant_key";

$ptr = $ffi->rust_pay_sign_md5($params, $key, 1); // 1 for uppercase
$md5Signature = getStringAndFree($ffi, $ptr);

echo "   Params: $params\n";
echo "   Key: $key\n";
echo "   Signature: $md5Signature\n\n";

// Example 2: Hex to Decimal Conversion
echo "2. Hex to Decimal Conversion\n";
$hexValue = "0xFF";
$ptr = $ffi->rust_pay_hex_to_dec($hexValue);
$decValue = getStringAndFree($ffi, $ptr);

echo "   Hex: $hexValue\n";
echo "   Decimal: $decValue\n\n";

// Example 3: RSA SHA256 Signature (would need real keys)
echo "3. RSA SHA256 Signature\n";
echo "   Note: This example requires real RSA keys.\n";
echo "   In production, you would:\n";
echo "   - Load your private key from file or config\n";
echo "   - Call rust_pay_sign_rsa_sha256(content, privateKey)\n";
echo "   - Use the returned signature for API requests\n\n";

// Example 4: Performance Comparison
echo "4. Performance Comparison (MD5)\n";

$iterations = 10000;
$testParams = "key1=value1&key2=value2&key3=value3&key4=value4&key5=value5";
$testKey = "performance_test_key";

// PHP native MD5
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $phpResult = strtoupper(md5($testParams . '&key=' . $testKey));
}
$phpTime = microtime(true) - $start;

// Rust MD5
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $ptr = $ffi->rust_pay_sign_md5($testParams, $testKey, 1);
    $rustResult = getStringAndFree($ffi, $ptr);
}
$rustTime = microtime(true) - $start;

echo "   Iterations: $iterations\n";
echo "   PHP Time: " . number_format($phpTime, 6) . "s\n";
echo "   Rust Time: " . number_format($rustTime, 6) . "s\n";
echo "   Speedup: " . number_format($phpTime / $rustTime, 2) . "x\n";
echo "   Results Match: " . ($phpResult === $rustResult ? 'Yes' : 'No') . "\n\n";

echo "=== Demo Complete ===\n";
