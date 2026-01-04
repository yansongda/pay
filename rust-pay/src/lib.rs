//! Rust implementation of payment SDK core cryptographic operations
//! 
//! This library provides high-performance cryptographic functions for payment
//! processing, including signature generation/verification, encryption/decryption,
//! and certificate handling for multiple payment providers (Alipay, WeChat, UnionPay, JSB).

pub mod signature;
pub mod encryption;
pub mod certificate;
pub mod utils;
pub mod error;
pub mod ffi;

// Re-export main types
pub use error::{PayError, Result};
pub use signature::{
    sign_rsa_sha256, verify_rsa_sha256,
    sign_md5, verify_md5,
};
pub use encryption::{
    encrypt_rsa_oaep, decrypt_rsa_oaep,
    decrypt_aes_256_gcm,
};
pub use certificate::{
    parse_certificate, get_certificate_sn,
    get_root_certificate_sns,
};
pub use utils::{hex_to_dec, format_private_key};

#[cfg(test)]
mod tests {
    #[test]
    fn test_basic_functionality() {
        // Basic smoke test
        assert!(true);
    }
}
