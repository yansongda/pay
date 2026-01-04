//! Signature generation and verification functions

use base64::{Engine as _, engine::general_purpose::STANDARD as BASE64};
use openssl::hash::MessageDigest;
use openssl::pkey::PKey;
use openssl::sign::{Signer, Verifier};
use md5::Md5;
use md5::Digest;
use crate::error::{PayError, Result};

/// Sign content using RSA with SHA256
///
/// # Arguments
/// * `content` - The content to sign
/// * `private_key_pem` - PEM-encoded private key
///
/// # Returns
/// Base64-encoded signature
pub fn sign_rsa_sha256(content: &str, private_key_pem: &str) -> Result<String> {
    let private_key = PKey::private_key_from_pem(private_key_pem.as_bytes())
        .map_err(|e| PayError::SignatureError(format!("Failed to parse private key: {}", e)))?;

    let mut signer = Signer::new(MessageDigest::sha256(), &private_key)
        .map_err(|e| PayError::SignatureError(format!("Failed to create signer: {}", e)))?;

    signer
        .update(content.as_bytes())
        .map_err(|e| PayError::SignatureError(format!("Failed to update signer: {}", e)))?;

    let signature = signer
        .sign_to_vec()
        .map_err(|e| PayError::SignatureError(format!("Failed to sign: {}", e)))?;

    Ok(BASE64.encode(&signature))
}

/// Verify RSA SHA256 signature
///
/// # Arguments
/// * `content` - The content that was signed
/// * `signature` - Base64-encoded signature
/// * `public_key_pem` - PEM-encoded public key
///
/// # Returns
/// `Ok(())` if signature is valid, error otherwise
pub fn verify_rsa_sha256(content: &str, signature: &str, public_key_pem: &str) -> Result<()> {
    let public_key = PKey::public_key_from_pem(public_key_pem.as_bytes())
        .map_err(|e| PayError::VerificationError(format!("Failed to parse public key: {}", e)))?;

    let signature_bytes = BASE64
        .decode(signature)
        .map_err(|e| PayError::VerificationError(format!("Failed to decode signature: {}", e)))?;

    let mut verifier = Verifier::new(MessageDigest::sha256(), &public_key)
        .map_err(|e| PayError::VerificationError(format!("Failed to create verifier: {}", e)))?;

    verifier
        .update(content.as_bytes())
        .map_err(|e| PayError::VerificationError(format!("Failed to update verifier: {}", e)))?;

    let valid = verifier
        .verify(&signature_bytes)
        .map_err(|e| PayError::VerificationError(format!("Failed to verify: {}", e)))?;

    if valid {
        Ok(())
    } else {
        Err(PayError::VerificationError("Signature verification failed".to_string()))
    }
}

/// Generate MD5-based signature for WeChat V2 API
///
/// # Arguments
/// * `params` - Key-value pairs to sign (already sorted)
/// * `key` - Merchant secret key
/// * `uppercase` - Whether to return uppercase signature
///
/// # Returns
/// MD5 signature string
pub fn sign_md5(params: &str, key: &str, uppercase: bool) -> String {
    let sign_string = format!("{}&key={}", params, key);
    let mut hasher = Md5::new();
    hasher.update(sign_string.as_bytes());
    let result = hasher.finalize();
    let hex_string = format!("{:x}", result);
    
    if uppercase {
        hex_string.to_uppercase()
    } else {
        hex_string
    }
}

/// Verify MD5-based signature
///
/// # Arguments
/// * `params` - Key-value pairs that were signed
/// * `key` - Merchant secret key
/// * `signature` - The signature to verify
/// * `uppercase` - Whether signature is uppercase
///
/// # Returns
/// `Ok(())` if signature is valid, error otherwise
pub fn verify_md5(params: &str, key: &str, signature: &str, uppercase: bool) -> Result<()> {
    let expected = sign_md5(params, key, uppercase);
    
    if expected == signature {
        Ok(())
    } else {
        Err(PayError::VerificationError("MD5 signature verification failed".to_string()))
    }
}

/// Sign content using RSA with SHA1 (for legacy systems)
///
/// # Arguments
/// * `content` - The content to sign
/// * `private_key_pem` - PEM-encoded private key
///
/// # Returns
/// Base64-encoded signature
pub fn sign_rsa_sha1(content: &str, private_key_pem: &str) -> Result<String> {
    let private_key = PKey::private_key_from_pem(private_key_pem.as_bytes())
        .map_err(|e| PayError::SignatureError(format!("Failed to parse private key: {}", e)))?;

    let mut signer = Signer::new(MessageDigest::sha1(), &private_key)
        .map_err(|e| PayError::SignatureError(format!("Failed to create signer: {}", e)))?;

    signer
        .update(content.as_bytes())
        .map_err(|e| PayError::SignatureError(format!("Failed to update signer: {}", e)))?;

    let signature = signer
        .sign_to_vec()
        .map_err(|e| PayError::SignatureError(format!("Failed to sign: {}", e)))?;

    Ok(BASE64.encode(&signature))
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_md5_sign() {
        let params = "amount=100&merchant_id=12345&order_no=ORDER001";
        let key = "test_key";
        let signature = sign_md5(params, key, true);
        assert!(!signature.is_empty());
        assert_eq!(signature.len(), 32); // MD5 produces 32 hex chars
    }

    #[test]
    fn test_md5_verify() {
        let params = "amount=100&merchant_id=12345&order_no=ORDER001";
        let key = "test_key";
        let signature = sign_md5(params, key, false);
        assert!(verify_md5(params, key, &signature, false).is_ok());
    }

    #[test]
    fn test_md5_verify_fail() {
        let params = "amount=100&merchant_id=12345&order_no=ORDER001";
        let key = "test_key";
        let wrong_sig = "invalid_signature";
        assert!(verify_md5(params, key, wrong_sig, false).is_err());
    }
}
