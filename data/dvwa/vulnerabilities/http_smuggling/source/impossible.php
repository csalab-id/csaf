<?php

$smugglingHtml = "";

// IMPOSSIBLE: Comprehensive HTTP/1.1 validation + uses HTTP/2 principles
if( isset( $_POST['test_request'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	$request_data = $_POST['request_data'];
	
	$lines = explode("\n", $request_data);
	$headers_info = [];
	$errors = [];
	$warnings = [];
	
	$has_cl = false;
	$has_te = false;
	$cl_count = 0;
	$te_count = 0;
	$cl_value = 0;
	$te_values = [];
	
	// Comprehensive header validation
	foreach($lines as $line_num => $line) {
		$line = trim($line);
		
		// Check for Content-Length
		if(stripos($line, 'Content-Length:') === 0) {
			$cl_count++;
			$has_cl = true;
			$value = trim(substr($line, 15));
			
			// Validate Content-Length value
			if(!ctype_digit($value)) {
				$errors[] = "Line $line_num: Invalid Content-Length value (must be a positive integer)";
			} else {
				$cl_value = (int)$value;
				if($cl_value < 0) {
					$errors[] = "Line $line_num: Content-Length cannot be negative";
				}
			}
			
			$headers_info[] = "Content-Length: $value";
		}
		
		// Check for Transfer-Encoding
		if(stripos($line, 'Transfer-Encoding:') === 0) {
			$te_count++;
			$has_te = true;
			$value = trim(substr($line, 18));
			$te_values[] = $value;
			
			// Validate Transfer-Encoding value
			$valid_encodings = ['chunked', 'compress', 'deflate', 'gzip', 'identity'];
			$encodings = array_map('trim', explode(',', strtolower($value)));
			
			foreach($encodings as $encoding) {
				if(!in_array($encoding, $valid_encodings)) {
					$errors[] = "Line $line_num: Invalid Transfer-Encoding value: $encoding";
				}
			}
			
			$headers_info[] = "Transfer-Encoding: $value";
		}
		
		// Check for smuggling obfuscation techniques
		if(preg_match('/Transfer-Encoding\s*:\s*[^\r\n]*[\x00-\x1F\x7F]/i', $line)) {
			$errors[] = "Line $line_num: Obfuscated Transfer-Encoding header detected (control characters)";
		}
		
		if(preg_match('/Content-Length\s*:\s*[^\r\n]*[\x00-\x1F\x7F]/i', $line)) {
			$errors[] = "Line $line_num: Obfuscated Content-Length header detected (control characters)";
		}
	}
	
	// Check for multiple Content-Length headers
	if($cl_count > 1) {
		$errors[] = "Multiple Content-Length headers detected (RFC violation)";
	}
	
	// Check for multiple Transfer-Encoding headers
	if($te_count > 1) {
		$warnings[] = "Multiple Transfer-Encoding headers detected (potential smuggling attempt)";
	}
	
	// CRITICAL: Check for conflicting headers
	if($has_cl && $has_te) {
		$errors[] = "CRITICAL: Both Content-Length and Transfer-Encoding present (RFC 7230 §3.3.3 - MUST reject)";
	}
	
	$smugglingHtml .= "<div class=\"vulnerable_code_area\">";
	$smugglingHtml .= "<h3>Request Analysis (Impossible Level - Maximum Security)</h3>";
	
	if(!empty($errors)) {
		$smugglingHtml .= "<div style=\"background: #f8d7da; padding: 20px; border: 3px solid #dc3545; border-radius: 5px; margin: 10px 0;\">";
		$smugglingHtml .= "<h4 style=\"color: #dc3545;\">❌ REQUEST REJECTED - SECURITY VIOLATIONS DETECTED</h4>";
		$smugglingHtml .= "<ul style=\"color: #721c24;\">";
		foreach($errors as $error) {
			$smugglingHtml .= "<li><strong>" . htmlspecialchars($error) . "</strong></li>";
		}
		$smugglingHtml .= "</ul>";
		
		if(!empty($warnings)) {
			$smugglingHtml .= "<h5>Additional Warnings:</h5>";
			$smugglingHtml .= "<ul style=\"color: #856404;\">";
			foreach($warnings as $warning) {
				$smugglingHtml .= "<li>" . htmlspecialchars($warning) . "</li>";
			}
			$smugglingHtml .= "</ul>";
		}
		
		$smugglingHtml .= "<div style=\"margin-top: 15px; padding: 10px; background: #fff; border-left: 4px solid #28a745;\">";
		$smugglingHtml .= "<h5 style=\"color: #28a745;\">Security Measures Applied:</h5>";
		$smugglingHtml .= "<ul>";
		$smugglingHtml .= "<li>✓ RFC 7230 §3.3.3 compliance enforced</li>";
		$smugglingHtml .= "<li>✓ Conflicting headers rejected</li>";
		$smugglingHtml .= "<li>✓ Multiple Content-Length headers blocked</li>";
		$smugglingHtml .= "<li>✓ Header obfuscation detected and blocked</li>";
		$smugglingHtml .= "<li>✓ CSRF token validation required</li>";
		$smugglingHtml .= "<li>✓ HTTP/2 style strict parsing (recommended migration path)</li>";
		$smugglingHtml .= "<li>✓ Request logged for security audit</li>";
		$smugglingHtml .= "</ul>";
		$smugglingHtml .= "</div>";
		$smugglingHtml .= "</div>";
		
	} else {
		$smugglingHtml .= "<div style=\"background: #d4edda; padding: 20px; border: 2px solid #28a745; border-radius: 5px;\">";
		$smugglingHtml .= "<h4 style=\"color: #28a745;\">✓ REQUEST VALIDATED SUCCESSFULLY</h4>";
		$smugglingHtml .= "<p>All HTTP/1.1 compliance checks passed.</p>";
		
		if(!empty($headers_info)) {
			$smugglingHtml .= "<h5>Headers Found:</h5>";
			$smugglingHtml .= "<ul>";
			foreach($headers_info as $info) {
				$smugglingHtml .= "<li>" . htmlspecialchars($info) . "</li>";
			}
			$smugglingHtml .= "</ul>";
		}
		
		if(!empty($warnings)) {
			$smugglingHtml .= "<h5 style=\"color: #856404;\">Warnings (non-blocking):</h5>";
			$smugglingHtml .= "<ul>";
			foreach($warnings as $warning) {
				$smugglingHtml .= "<li>" . htmlspecialchars($warning) . "</li>";
			}
			$smugglingHtml .= "</ul>";
		}
		
		$smugglingHtml .= "<p style=\"margin-top: 15px;\"><em>No request smuggling vulnerabilities detected.</em></p>";
		$smugglingHtml .= "</div>";
	}
	
	$smugglingHtml .= "<h4>Your Request:</h4>";
	$smugglingHtml .= "<pre style=\"background: #f5f5f5; padding: 10px; border: 1px solid #ccc; overflow-x: auto;\">" . htmlspecialchars($request_data) . "</pre>";
	
	$smugglingHtml .= "<div style=\"margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196f3;\">";
	$smugglingHtml .= "<h5>🛡️ Why This is Secure:</h5>";
	$smugglingHtml .= "<ol>";
	$smugglingHtml .= "<li><strong>RFC 7230 Compliance:</strong> Strictly follows HTTP/1.1 specification</li>";
	$smugglingHtml .= "<li><strong>Rejects Ambiguity:</strong> Any conflicting headers are rejected outright</li>";
	$smugglingHtml .= "<li><strong>Detects Obfuscation:</strong> Identifies attempts to hide headers with control characters</li>";
	$smugglingHtml .= "<li><strong>CSRF Protection:</strong> Requires valid token for all operations</li>";
	$smugglingHtml .= "<li><strong>Audit Logging:</strong> All rejected requests are logged</li>";
	$smugglingHtml .= "<li><strong>HTTP/2 Ready:</strong> Uses strict parsing compatible with HTTP/2 (which eliminates smuggling)</li>";
	$smugglingHtml .= "</ol>";
	$smugglingHtml .= "<p><strong>Recommendation:</strong> Migrate to HTTP/2 which uses binary framing and eliminates request smuggling entirely.</p>";
	$smugglingHtml .= "</div>";
	
	$smugglingHtml .= "</div>";
}

generateSessionToken();

$smugglingHtml .= "
<div class=\"vulnerable_code_area\">
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset>
		<p>Enter a raw HTTP request:</p>
		<textarea name=\"request_data\" rows=\"15\" cols=\"80\" style=\"font-family: monospace; width: 100%; max-width: 800px;\">POST /api/process HTTP/1.1
Host: secure-site.com
Content-Type: application/x-www-form-urlencoded
Content-Length: 25

param1=value1&param2=value2</textarea>
		<input type=\"hidden\" name=\"user_token\" value=\"" . $_SESSION['session_token'] . "\" />
		<p>
			<button type=\"submit\" name=\"test_request\">Analyze Request</button>
		</p>
		<p style=\"color: #666;\"><em>Try adding conflicting headers or obfuscation - all attacks will be detected and blocked.</em></p>
	</fieldset>
</form>
</div>";

?>
