//! C FFI bindings for PHP integration
//! 
//! This module provides C-compatible function exports that can be called
//! from PHP using FFI (Foreign Function Interface).

use std::ffi::{CStr, CString};
use std::os::raw::c_char;
use std::ptr;

use crate::{
    sign_rsa_sha256, verify_rsa_sha256,
    sign_md5, 
    encrypt_rsa_oaep, decrypt_rsa_oaep,
    decrypt_aes_256_gcm,
    get_certificate_sn, get_root_certificate_sns,
    hex_to_dec,
};

/// Convert Rust String to C string pointer (caller must free)
fn rust_string_to_c(s: String) -> *mut c_char {
    CString::new(s).unwrap_or_default().into_raw()
}

/// Free a C string allocated by Rust
#[no_mangle]
pub extern "C" fn rust_pay_free_string(s: *mut c_char) {
    if !s.is_null() {
        unsafe {
            let _ = CString::from_raw(s);
        }
    }
}

/// Sign content with RSA SHA256
/// Returns base64-encoded signature or NULL on error
#[no_mangle]
pub extern "C" fn rust_pay_sign_rsa_sha256(
    content: *const c_char,
    private_key: *const c_char,
) -> *mut c_char {
    if content.is_null() || private_key.is_null() {
        return ptr::null_mut();
    }

    let content_str = unsafe { CStr::from_ptr(content).to_string_lossy() };
    let key_str = unsafe { CStr::from_ptr(private_key).to_string_lossy() };

    match sign_rsa_sha256(&content_str, &key_str) {
        Ok(signature) => rust_string_to_c(signature),
        Err(_) => ptr::null_mut(),
    }
}

/// Verify RSA SHA256 signature
/// Returns 1 if valid, 0 if invalid or error
#[no_mangle]
pub extern "C" fn rust_pay_verify_rsa_sha256(
    content: *const c_char,
    signature: *const c_char,
    public_key: *const c_char,
) -> i32 {
    if content.is_null() || signature.is_null() || public_key.is_null() {
        return 0;
    }

    let content_str = unsafe { CStr::from_ptr(content).to_string_lossy() };
    let sig_str = unsafe { CStr::from_ptr(signature).to_string_lossy() };
    let key_str = unsafe { CStr::from_ptr(public_key).to_string_lossy() };

    match verify_rsa_sha256(&content_str, &sig_str, &key_str) {
        Ok(_) => 1,
        Err(_) => 0,
    }
}

/// Generate MD5 signature
/// Returns MD5 hash string or NULL on error
#[no_mangle]
pub extern "C" fn rust_pay_sign_md5(
    params: *const c_char,
    key: *const c_char,
    uppercase: i32,
) -> *mut c_char {
    if params.is_null() || key.is_null() {
        return ptr::null_mut();
    }

    let params_str = unsafe { CStr::from_ptr(params).to_string_lossy() };
    let key_str = unsafe { CStr::from_ptr(key).to_string_lossy() };

    let signature = sign_md5(&params_str, &key_str, uppercase != 0);
    rust_string_to_c(signature)
}

/// Decrypt AES-256-GCM encrypted data
/// Returns decrypted string or NULL on error
#[no_mangle]
pub extern "C" fn rust_pay_decrypt_aes_256_gcm(
    ciphertext: *const c_char,
    key: *const c_char,
    nonce: *const c_char,
    aad: *const c_char,
) -> *mut c_char {
    if ciphertext.is_null() || key.is_null() || nonce.is_null() || aad.is_null() {
        return ptr::null_mut();
    }

    let ct_str = unsafe { CStr::from_ptr(ciphertext).to_string_lossy() };
    let key_str = unsafe { CStr::from_ptr(key).to_string_lossy() };
    let nonce_str = unsafe { CStr::from_ptr(nonce).to_string_lossy() };
    let aad_str = unsafe { CStr::from_ptr(aad).to_string_lossy() };

    match decrypt_aes_256_gcm(&ct_str, &key_str, &nonce_str, &aad_str) {
        Ok(plaintext) => rust_string_to_c(plaintext),
        Err(_) => ptr::null_mut(),
    }
}

/// Get certificate serial number
/// Returns SN string or NULL on error
#[no_mangle]
pub extern "C" fn rust_pay_get_certificate_sn(cert_pem: *const c_char) -> *mut c_char {
    if cert_pem.is_null() {
        return ptr::null_mut();
    }

    let cert_str = unsafe { CStr::from_ptr(cert_pem).to_string_lossy() };

    match get_certificate_sn(&cert_str) {
        Ok(sn) => rust_string_to_c(sn),
        Err(_) => ptr::null_mut(),
    }
}

/// Convert hex to decimal
/// Returns decimal string or NULL on error
#[no_mangle]
pub extern "C" fn rust_pay_hex_to_dec(hex: *const c_char) -> *mut c_char {
    if hex.is_null() {
        return ptr::null_mut();
    }

    let hex_str = unsafe { CStr::from_ptr(hex).to_string_lossy() };

    match hex_to_dec(&hex_str) {
        Ok(dec) => rust_string_to_c(dec),
        Err(_) => ptr::null_mut(),
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_string_conversion() {
        let rust_str = "test string".to_string();
        let c_str = rust_string_to_c(rust_str);
        assert!(!c_str.is_null());
        rust_pay_free_string(c_str);
    }
}
