//! Utility functions

use num_bigint::BigInt;
use crate::error::{PayError, Result};

/// Convert hexadecimal string to decimal string
///
/// This is used for certificate serial number conversion where the serial
/// might start with "0x" and needs to be converted to decimal format.
///
/// # Arguments
/// * `hex_str` - Hexadecimal string (with or without "0x" prefix)
///
/// # Returns
/// Decimal string representation
pub fn hex_to_dec(hex_str: &str) -> Result<String> {
    // Remove "0x" prefix if present
    let hex_clean = hex_str.trim_start_matches("0x").trim_start_matches("0X");
    
    // Parse as BigInt from hex
    let bigint = BigInt::parse_bytes(hex_clean.as_bytes(), 16)
        .ok_or_else(|| PayError::InvalidInputError(format!("Invalid hex string: {}", hex_str)))?;
    
    Ok(bigint.to_string())
}

/// Format private key to PEM format
///
/// # Arguments
/// * `key` - Private key string (can be raw key or PEM format)
///
/// # Returns
/// Properly formatted PEM private key
pub fn format_private_key(key: &str) -> String {
    // If already in PEM format, return as-is
    if key.starts_with("-----BEGIN") {
        return key.to_string();
    }
    
    // Otherwise, wrap in PEM markers
    format!(
        "-----BEGIN RSA PRIVATE KEY-----\n{}\n-----END RSA PRIVATE KEY-----",
        wrap_text(key, 64)
    )
}

/// Format public key to PEM format
///
/// # Arguments
/// * `key` - Public key string (can be raw key or PEM format)
///
/// # Returns
/// Properly formatted PEM public key
pub fn format_public_key(key: &str) -> String {
    // If already in PEM format, return as-is
    if key.starts_with("-----BEGIN") {
        return key.to_string();
    }
    
    // Otherwise, wrap in PEM markers
    format!(
        "-----BEGIN PUBLIC KEY-----\n{}\n-----END PUBLIC KEY-----",
        wrap_text(key, 64)
    )
}

/// Wrap text to specified line width
///
/// # Arguments
/// * `text` - Text to wrap
/// * `width` - Maximum line width
///
/// # Returns
/// Wrapped text with newlines
fn wrap_text(text: &str, width: usize) -> String {
    text.chars()
        .collect::<Vec<_>>()
        .chunks(width)
        .map(|chunk| chunk.iter().collect::<String>())
        .collect::<Vec<_>>()
        .join("\n")
}

/// Sort and format parameters as query string
///
/// # Arguments
/// * `params` - Key-value pairs
///
/// # Returns
/// Sorted and formatted query string (e.g., "key1=value1&key2=value2")
pub fn format_params_string(params: &[(&str, &str)]) -> String {
    let mut sorted_params: Vec<_> = params
        .iter()
        .filter(|(k, v)| !k.is_empty() && !v.is_empty())
        .collect();
    
    sorted_params.sort_by(|a, b| a.0.cmp(b.0));
    
    sorted_params
        .iter()
        .map(|(k, v)| format!("{}={}", k, v))
        .collect::<Vec<_>>()
        .join("&")
}

/// Build sign string from sorted parameters (excluding sign field)
///
/// # Arguments
/// * `params` - Key-value pairs
/// * `exclude_keys` - Keys to exclude (typically "sign")
///
/// # Returns
/// Formatted string for signing
pub fn build_sign_string(params: &[(&str, &str)], exclude_keys: &[&str]) -> String {
    let filtered_params: Vec<_> = params
        .iter()
        .filter(|(k, v)| !exclude_keys.contains(k) && !k.is_empty() && !v.is_empty())
        .copied()
        .collect();
    
    format_params_string(&filtered_params)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_hex_to_dec() {
        assert_eq!(hex_to_dec("10").unwrap(), "16");
        assert_eq!(hex_to_dec("0x10").unwrap(), "16");
        assert_eq!(hex_to_dec("FF").unwrap(), "255");
        assert_eq!(hex_to_dec("0xFF").unwrap(), "255");
    }

    #[test]
    fn test_hex_to_dec_large() {
        let hex = "123456789ABCDEF";
        let dec = hex_to_dec(hex).unwrap();
        assert_eq!(dec, "81985529216486895");
    }

    #[test]
    fn test_format_private_key_raw() {
        let raw_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A";
        let formatted = format_private_key(raw_key);
        assert!(formatted.starts_with("-----BEGIN RSA PRIVATE KEY-----"));
        assert!(formatted.ends_with("-----END RSA PRIVATE KEY-----"));
    }

    #[test]
    fn test_format_private_key_already_pem() {
        let pem_key = "-----BEGIN PRIVATE KEY-----\ntest\n-----END PRIVATE KEY-----";
        let formatted = format_private_key(pem_key);
        assert_eq!(formatted, pem_key);
    }

    #[test]
    fn test_wrap_text() {
        let text = "ABCDEFGHIJ";
        let wrapped = wrap_text(text, 3);
        assert_eq!(wrapped, "ABC\nDEF\nGHI\nJ");
    }

    #[test]
    fn test_format_params_string() {
        let params = vec![
            ("key3", "value3"),
            ("key1", "value1"),
            ("key2", "value2"),
        ];
        let formatted = format_params_string(&params);
        assert_eq!(formatted, "key1=value1&key2=value2&key3=value3");
    }

    #[test]
    fn test_format_params_string_filters_empty() {
        let params = vec![
            ("key1", "value1"),
            ("key2", ""),
            ("", "value3"),
            ("key4", "value4"),
        ];
        let formatted = format_params_string(&params);
        assert_eq!(formatted, "key1=value1&key4=value4");
    }

    #[test]
    fn test_build_sign_string() {
        let params = vec![
            ("key3", "value3"),
            ("key1", "value1"),
            ("sign", "should_be_excluded"),
            ("key2", "value2"),
        ];
        let formatted = build_sign_string(&params, &["sign"]);
        assert_eq!(formatted, "key1=value1&key2=value2&key3=value3");
        assert!(!formatted.contains("sign"));
    }
}
