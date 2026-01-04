//! Error types for the payment SDK

use thiserror::Error;

/// Result type alias for the payment SDK
pub type Result<T> = std::result::Result<T, PayError>;

/// Error types for payment operations
#[derive(Error, Debug)]
pub enum PayError {
    #[error("Signature error: {0}")]
    SignatureError(String),

    #[error("Verification error: {0}")]
    VerificationError(String),

    #[error("Encryption error: {0}")]
    EncryptionError(String),

    #[error("Decryption error: {0}")]
    DecryptionError(String),

    #[error("Certificate parsing error: {0}")]
    CertificateError(String),

    #[error("Invalid configuration: {0}")]
    ConfigError(String),

    #[error("Base64 decode error: {0}")]
    Base64Error(#[from] base64::DecodeError),

    #[error("OpenSSL error: {0}")]
    OpenSSLError(#[from] openssl::error::ErrorStack),

    #[error("Invalid key format: {0}")]
    InvalidKeyError(String),

    #[error("Invalid input: {0}")]
    InvalidInputError(String),
}
