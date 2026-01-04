//! Benchmark tests for Rust Pay Library
//! 
//! Run with: cargo bench

use criterion::{black_box, criterion_group, criterion_main, Criterion};
use rust_pay::{
    sign_md5, sign_rsa_sha256, verify_rsa_sha256,
    hex_to_dec, format_private_key,
};

// Generate a test RSA key pair for benchmarking
fn generate_test_keypair() -> (String, String) {
    use openssl::rsa::Rsa;
    use openssl::pkey::PKey;
    
    let rsa = Rsa::generate(2048).unwrap();
    let private_key = PKey::from_rsa(rsa.clone()).unwrap();
    let public_key = PKey::from_rsa(rsa.clone()).unwrap();
    
    let private_pem = private_key.private_key_to_pem_pkcs8().unwrap();
    let public_pem = public_key.public_key_to_pem().unwrap();
    
    (
        String::from_utf8(private_pem).unwrap(),
        String::from_utf8(public_pem).unwrap(),
    )
}

fn bench_md5_signature(c: &mut Criterion) {
    let params = "amount=100&merchant_id=12345&order_no=ORDER001&timestamp=1234567890";
    let key = "test_merchant_secret_key";
    
    c.bench_function("md5_sign_uppercase", |b| {
        b.iter(|| sign_md5(black_box(params), black_box(key), black_box(true)))
    });
    
    c.bench_function("md5_sign_lowercase", |b| {
        b.iter(|| sign_md5(black_box(params), black_box(key), black_box(false)))
    });
}

fn bench_rsa_signature(c: &mut Criterion) {
    let (private_key, public_key) = generate_test_keypair();
    let content = "key1=value1&key2=value2&key3=value3&timestamp=1234567890";
    
    c.bench_function("rsa_sha256_sign", |b| {
        b.iter(|| {
            sign_rsa_sha256(black_box(content), black_box(&private_key)).unwrap()
        })
    });
    
    let signature = sign_rsa_sha256(content, &private_key).unwrap();
    
    c.bench_function("rsa_sha256_verify", |b| {
        b.iter(|| {
            verify_rsa_sha256(
                black_box(content),
                black_box(&signature),
                black_box(&public_key),
            ).unwrap()
        })
    });
}

fn bench_hex_conversion(c: &mut Criterion) {
    let hex_small = "0xFF";
    let hex_large = "123456789ABCDEF0123456789ABCDEF0";
    
    c.bench_function("hex_to_dec_small", |b| {
        b.iter(|| hex_to_dec(black_box(hex_small)).unwrap())
    });
    
    c.bench_function("hex_to_dec_large", |b| {
        b.iter(|| hex_to_dec(black_box(hex_large)).unwrap())
    });
}

fn bench_key_formatting(c: &mut Criterion) {
    let raw_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA";
    
    c.bench_function("format_private_key", |b| {
        b.iter(|| format_private_key(black_box(raw_key)))
    });
}

criterion_group!(
    benches,
    bench_md5_signature,
    bench_rsa_signature,
    bench_hex_conversion,
    bench_key_formatting,
);

criterion_main!(benches);
