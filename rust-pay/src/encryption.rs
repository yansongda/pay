//! Encryption and decryption functions

use base64::{Engine as _, engine::general_purpose::STANDARD as BASE64};
use openssl::rsa::Padding;
use openssl::pkey::PKey;
use openssl::symm::{decrypt_aead, Cipher};
use crate::error::{PayError, Result};

const WECHAT_AUTH_TAG_LENGTH: usize = 16;

/// Encrypt data using RSA with PKCS1 OAEP padding
///
/// # Arguments
/// * `plaintext` - The data to encrypt
/// * `public_key_pem` - PEM-encoded public key
///
/// # Returns
/// Base64-encoded encrypted data
pub fn encrypt_rsa_oaep(plaintext: &str, public_key_pem: &str) -> Result<String> {
    let public_key = PKey::public_key_from_pem(public_key_pem.as_bytes())
        .map_err(|e| PayError::EncryptionError(format!("Failed to parse public key: {}", e)))?;

    let rsa = public_key.rsa()
        .map_err(|e| PayError::EncryptionError(format!("Failed to get RSA key: {}", e)))?;

    let mut encrypted = vec![0u8; rsa.size() as usize];
    let len = rsa
        .public_encrypt(plaintext.as_bytes(), &mut encrypted, Padding::PKCS1_OAEP)
        .map_err(|e| PayError::EncryptionError(format!("Failed to encrypt: {}", e)))?;

    encrypted.truncate(len);
    Ok(BASE64.encode(&encrypted))
}

/// Decrypt data using RSA with PKCS1 OAEP padding
///
/// # Arguments
/// * `ciphertext_base64` - Base64-encoded encrypted data
/// * `private_key_pem` - PEM-encoded private key
///
/// # Returns
/// Decrypted plaintext
pub fn decrypt_rsa_oaep(ciphertext_base64: &str, private_key_pem: &str) -> Result<String> {
    let ciphertext = BASE64
        .decode(ciphertext_base64)
        .map_err(|e| PayError::DecryptionError(format!("Failed to decode ciphertext: {}", e)))?;

    let private_key = PKey::private_key_from_pem(private_key_pem.as_bytes())
        .map_err(|e| PayError::DecryptionError(format!("Failed to parse private key: {}", e)))?;

    let rsa = private_key.rsa()
        .map_err(|e| PayError::DecryptionError(format!("Failed to get RSA key: {}", e)))?;

    let mut decrypted = vec![0u8; rsa.size() as usize];
    let len = rsa
        .private_decrypt(&ciphertext, &mut decrypted, Padding::PKCS1_OAEP)
        .map_err(|e| PayError::DecryptionError(format!("Failed to decrypt: {}", e)))?;

    decrypted.truncate(len);
    String::from_utf8(decrypted)
        .map_err(|e| PayError::DecryptionError(format!("Failed to decode UTF-8: {}", e)))
}

/// Decrypt WeChat resource using AES-256-GCM
///
/// # Arguments
/// * `ciphertext_base64` - Base64-encoded ciphertext (includes auth tag at end)
/// * `key` - 32-byte secret key
/// * `nonce` - Nonce/IV for GCM mode
/// * `associated_data` - Additional authenticated data
///
/// # Returns
/// Decrypted plaintext (JSON string or certificate)
pub fn decrypt_aes_256_gcm(
    ciphertext_base64: &str,
    key: &str,
    nonce: &str,
    associated_data: &str,
) -> Result<String> {
    // Decode the base64 ciphertext
    let ciphertext_with_tag = BASE64
        .decode(ciphertext_base64)
        .map_err(|e| PayError::DecryptionError(format!("Failed to decode ciphertext: {}", e)))?;

    // Check minimum length
    if ciphertext_with_tag.len() <= WECHAT_AUTH_TAG_LENGTH {
        return Err(PayError::DecryptionError(
            "Ciphertext too short, must be longer than auth tag length".to_string()
        ));
    }

    // Validate key length (must be 32 bytes for AES-256)
    if key.len() != 32 {
        return Err(PayError::DecryptionError(
            format!("Invalid key length: expected 32 bytes, got {}", key.len())
        ));
    }

    // Split ciphertext and auth tag
    let ciphertext_len = ciphertext_with_tag.len() - WECHAT_AUTH_TAG_LENGTH;
    let ciphertext = &ciphertext_with_tag[..ciphertext_len];
    let tag = &ciphertext_with_tag[ciphertext_len..];

    // Perform AES-256-GCM decryption
    let decrypted = decrypt_aead(
        Cipher::aes_256_gcm(),
        key.as_bytes(),
        Some(nonce.as_bytes()),
        associated_data.as_bytes(),
        ciphertext,
        tag,
    )
    .map_err(|e| PayError::DecryptionError(format!("AES-GCM decryption failed: {}", e)))?;

    String::from_utf8(decrypted)
        .map_err(|e| PayError::DecryptionError(format!("Failed to decode UTF-8: {}", e)))
}

/// Hash content using SHA256
///
/// # Arguments
/// * `content` - The content to hash
///
/// # Returns
/// Hex-encoded SHA256 hash
pub fn hash_sha256(content: &str) -> String {
    use sha2::{Sha256, Digest};
    let mut hasher = Sha256::new();
    hasher.update(content.as_bytes());
    let result = hasher.finalize();
    format!("{:x}", result)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_hash_sha256() {
        let content = "test content";
        let hash = hash_sha256(content);
        assert_eq!(hash.len(), 64); // SHA256 produces 64 hex chars
    }

    #[test]
    fn test_decrypt_aes_256_gcm_invalid_key() {
        let result = decrypt_aes_256_gcm(
            "dGVzdA==",
            "short_key", // Invalid key length
            "nonce123",
            "aad",
        );
        assert!(result.is_err());
    }

    #[test]
    fn test_decrypt_aes_256_gcm_short_ciphertext() {
        let result = decrypt_aes_256_gcm(
            "dGVzdA==", // Too short
            "12345678901234567890123456789012", // 32 bytes
            "nonce123",
            "aad",
        );
        assert!(result.is_err());
    }
}
