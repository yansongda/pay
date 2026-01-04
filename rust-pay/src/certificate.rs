//! Certificate parsing and handling functions

use openssl::x509::X509;
use openssl::asn1::Asn1IntegerRef;
use md5::Md5;
use md5::Digest;
use crate::error::{PayError, Result};

/// Parse an X.509 certificate
///
/// # Arguments
/// * `cert_pem` - PEM-encoded certificate
///
/// # Returns
/// Parsed certificate information
pub fn parse_certificate(cert_pem: &str) -> Result<CertificateInfo> {
    let cert = X509::from_pem(cert_pem.as_bytes())
        .map_err(|e| PayError::CertificateError(format!("Failed to parse certificate: {}", e)))?;

    // Extract issuer
    let issuer = extract_issuer(&cert)?;
    
    // Extract serial number
    let serial_number = format_serial_number(cert.serial_number())?;
    let serial_hex = format_serial_hex(cert.serial_number())?;

    // Extract signature algorithm
    let signature_algorithm = cert.signature_algorithm().object().to_string();

    Ok(CertificateInfo {
        issuer,
        serial_number,
        serial_hex,
        signature_algorithm,
    })
}

/// Certificate information structure
#[derive(Debug, Clone)]
pub struct CertificateInfo {
    pub issuer: Vec<(String, String)>,
    pub serial_number: String,
    pub serial_hex: String,
    pub signature_algorithm: String,
}

/// Extract issuer from certificate
fn extract_issuer(cert: &X509) -> Result<Vec<(String, String)>> {
    let mut issuer = Vec::new();
    let issuer_name = cert.issuer_name();
    
    for entry in issuer_name.entries() {
        let key = entry.object().to_string();
        let value = entry.data().as_utf8()
            .map_err(|e| PayError::CertificateError(format!("Failed to extract issuer value: {}", e)))?;
        
        // Map OID to common names
        let key_name = match key.as_str() {
            "countryName" | "C" => "C",
            "stateOrProvinceName" | "ST" => "ST",
            "localityName" | "L" => "L",
            "organizationName" | "O" => "O",
            "organizationalUnitName" | "OU" => "OU",
            "commonName" | "CN" => "CN",
            _ => &key,
        };
        
        issuer.push((key_name.to_string(), value.to_string()));
    }
    
    Ok(issuer)
}

/// Format serial number as decimal string
fn format_serial_number(serial: &Asn1IntegerRef) -> Result<String> {
    let bn = serial.to_bn()
        .map_err(|e| PayError::CertificateError(format!("Failed to convert serial to BigNum: {}", e)))?;
    let hex = bn.to_hex_str()
        .map_err(|e| PayError::CertificateError(format!("Failed to convert BigNum to hex: {}", e)))?;
    
    // Convert hex to decimal
    crate::utils::hex_to_dec(&hex)
}

/// Format serial number as hex string
fn format_serial_hex(serial: &Asn1IntegerRef) -> Result<String> {
    let bn = serial.to_bn()
        .map_err(|e| PayError::CertificateError(format!("Failed to convert serial to BigNum: {}", e)))?;
    Ok(bn.to_hex_str()
        .map_err(|e| PayError::CertificateError(format!("Failed to convert BigNum to hex: {}", e)))?
        .to_string())
}

/// Calculate certificate serial number (SN) using Alipay's method
///
/// # Arguments
/// * `cert_pem` - PEM-encoded certificate
///
/// # Returns
/// MD5 hash of issuer and serial number
pub fn get_certificate_sn(cert_pem: &str) -> Result<String> {
    let cert_info = parse_certificate(cert_pem)?;
    
    // Format issuer as "key=value,key=value,..."
    let issuer_string = format_issuer(&cert_info.issuer);
    
    // Calculate MD5 of reversed issuer + serial number
    let content = format!("{}{}", issuer_string, cert_info.serial_number);
    let mut hasher = Md5::new();
    hasher.update(content.as_bytes());
    let result = hasher.finalize();
    
    Ok(format!("{:x}", result))
}

/// Get root certificate SNs from a multi-certificate PEM file
///
/// # Arguments
/// * `root_cert_pem` - PEM file containing multiple certificates
///
/// # Returns
/// Underscore-separated list of certificate SNs (for sha1/sha256 signed certs only)
pub fn get_root_certificate_sns(root_cert_pem: &str) -> Result<String> {
    let mut sns = Vec::new();
    
    // Split by certificate boundary
    let parts: Vec<&str> = root_cert_pem.split("-----END CERTIFICATE-----").collect();
    
    for part in parts {
        let trimmed = part.trim();
        if trimmed.is_empty() {
            continue;
        }
        
        // Reconstruct the certificate with the end marker
        let cert_pem = format!("{}-----END CERTIFICATE-----", trimmed);
        
        match parse_certificate(&cert_pem) {
            Ok(cert_info) => {
                // Check if signature algorithm is sha1 or sha256 with RSA
                if cert_info.signature_algorithm.contains("sha1") 
                    || cert_info.signature_algorithm.contains("sha256") {
                    
                    let issuer_string = format_issuer(&cert_info.issuer);
                    let content = format!("{}{}", issuer_string, cert_info.serial_number);
                    let mut hasher = Md5::new();
                    hasher.update(content.as_bytes());
                    let result = hasher.finalize();
                    sns.push(format!("{:x}", result));
                }
            }
            Err(_) => continue, // Skip invalid certificates
        }
    }
    
    Ok(sns.join("_"))
}

/// Format issuer as comma-separated key=value pairs
fn format_issuer(issuer: &[(String, String)]) -> String {
    // Reverse the issuer order to match Alipay's format
    let reversed: Vec<_> = issuer.iter().rev().collect();
    reversed
        .iter()
        .map(|(k, v)| format!("{}={}", k, v))
        .collect::<Vec<_>>()
        .join(",")
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_format_issuer() {
        let issuer = vec![
            ("CN".to_string(), "Test CA".to_string()),
            ("O".to_string(), "Test Org".to_string()),
            ("C".to_string(), "US".to_string()),
        ];
        let formatted = format_issuer(&issuer);
        assert!(formatted.contains("CN=Test CA"));
        assert!(formatted.contains("O=Test Org"));
    }
}
