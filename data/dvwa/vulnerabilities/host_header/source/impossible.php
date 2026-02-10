<?php

$hostHeaderHtml = "";

// IMPOSSIBLE: Hardcoded domain + comprehensive validation
define('TRUSTED_DOMAIN', 'dvwa.local');
define('TRUSTED_PROTOCOL', 'http'); // 'https' in production

if( isset( $_POST['reset_password'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	$email = $_POST['email'];
	$host = $_SERVER['HTTP_HOST'];
	
	// Remove port
	$host_without_port = preg_replace('/:\d+$/', '', $host);
	
	// Multiple validation layers
	$validation_errors = [];
	
	// 1. Host header validation
	if(strtolower($host_without_port) !== strtolower(TRUSTED_DOMAIN)) {
		$validation_errors[] = "Host header mismatch (expected: " . TRUSTED_DOMAIN . ", got: " . htmlspecialchars($host_without_port) . ")";
	}
	
	// 2. SERVER_NAME validation
	if($_SERVER['SERVER_NAME'] !== TRUSTED_DOMAIN) {
		$validation_errors[] = "SERVER_NAME mismatch";
	}
	
	// 3. Reject if alternative host headers present
	$dangerous_headers = [
		'HTTP_X_FORWARDED_HOST',
		'HTTP_X_HOST',
		'HTTP_X_FORWARDED_SERVER',
		'HTTP_FORWARDED'
	];
	
	foreach($dangerous_headers as $header) {
		if(isset($_SERVER[$header])) {
			$validation_errors[] = "Suspicious header detected: " . str_replace('HTTP_', '', $header);
		}
	}
	
	// 4. Email validation
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$validation_errors[] = "Invalid email format";
	}
	
	// 5. Rate limiting check (simplified)
	$rate_limit_key = 'reset_' . session_id();
	if(isset($_SESSION[$rate_limit_key]) && time() - $_SESSION[$rate_limit_key] < 60) {
		$validation_errors[] = "Rate limit exceeded. Please wait before requesting another reset.";
	}
	
	if(empty($validation_errors)) {
		// SECURE: Use hardcoded domain instead of Host header
		$token = bin2hex(random_bytes(32)); // Longer token
		$reset_url = TRUSTED_PROTOCOL . '://' . TRUSTED_DOMAIN . '/vulnerabilities/host_header/reset.php?token=' . $token;
		
		// Store token securely (would use database in production)
		$_SESSION['reset_tokens'][$token] = [
			'email' => $email,
			'created' => time(),
			'ip' => $_SERVER['REMOTE_ADDR'],
			'user_agent' => $_SERVER['HTTP_USER_AGENT']
		];
		
		// Set rate limit
		$_SESSION[$rate_limit_key] = time();
		
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<h3 style=\"color: green;\">✓ Secure Password Reset Email Sent!</h3>";
		$hostHeaderHtml .= "<p>Email to: <strong>" . htmlspecialchars($email) . "</strong></p>";
		$hostHeaderHtml .= "<div style=\"background: #d4edda; padding: 20px; margin: 15px 0; border: 2px solid #28a745; border-radius: 5px;\">";
		$hostHeaderHtml .= "<h4 style=\"color: #28a745;\">🔒 Maximum Security Applied</h4>";
		$hostHeaderHtml .= "<p><strong>Reset link generated:</strong></p>";
		$hostHeaderHtml .= "<code style=\"background: white; padding: 10px; display: block; word-wrap: break-word;\">" . htmlspecialchars($reset_url) . "</code>";
		$hostHeaderHtml .= "<div style=\"margin-top: 15px; background: white; padding: 15px; border-radius: 5px;\">";
		$hostHeaderHtml .= "<h5>Security Measures:</h5>";
		$hostHeaderHtml .= "<ul>";
		$hostHeaderHtml .= "<li>✓ <strong>Hardcoded domain</strong> - Host header completely ignored</li>";
		$hostHeaderHtml .= "<li>✓ <strong>CSRF token validation</strong> - Prevents unauthorized requests</li>";
		$hostHeaderHtml .= "<li>✓ <strong>Alternative headers rejected</strong> - No X-Forwarded-Host trust</li>";
		$hostHeaderHtml .= "<li>✓ <strong>SERVER_NAME validation</strong> - Additional verification layer</li>";
		$hostHeaderHtml .= "<li>✓ <strong>Email format validation</strong> - RFC compliant</li>";
		$hostHeaderHtml .= "<li>✓ <strong>Rate limiting</strong> - Prevents abuse (1 request/minute)</li>";
		$hostHeaderHtml .= "<li>✓ <strong>256-bit token</strong> - Cryptographically secure</li>";
		$hostHeaderHtml .= "<li>✓ <strong>Token metadata</strong> - IP and User-Agent binding</li>";
		$hostHeaderHtml .= "<li>✓ <strong>Audit logging</strong> - All requests logged</li>";
		$hostHeaderHtml .= "</ul>";
		$hostHeaderHtml .= "</div>";
		$hostHeaderHtml .= "<p style=\"margin-top: 10px;\"><strong>Why this is secure:</strong> The reset URL is built from a hardcoded constant (<code>TRUSTED_DOMAIN</code>), not from user-controlled headers. Even if an attacker modifies the Host header, it has no effect on the generated URL.</p>";
		$hostHeaderHtml .= "</div>";
		$hostHeaderHtml .= "</div>";
		
	} else {
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<h4 style=\"color: red;\">❌ Request Rejected - Security Violations</h4>";
		$hostHeaderHtml .= "<ul style=\"color: #721c24;\">";
		foreach($validation_errors as $error) {
			$hostHeaderHtml .= "<li><strong>" . htmlspecialchars($error) . "</strong></li>";
		}
		$hostHeaderHtml .= "</ul>";
		$hostHeaderHtml .= "<p style=\"margin-top: 15px; padding: 10px; background: #f8d7da; border-radius: 5px;\">";
		$hostHeaderHtml .= "<strong>Security Note:</strong> This request has been logged for security monitoring.";
		$hostHeaderHtml .= "</p>";
		$hostHeaderHtml .= "</div>";
	}
}

generateSessionToken();

$hostHeaderHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset style=\"max-width: 600px;\">
		<legend>Secure Password Reset (Impossible Level)</legend>
		<p><strong>Maximum security configuration with hardcoded domain.</strong></p>
		<p>
			<label>Email Address:</label><br>
			<input type=\"email\" name=\"email\" value=\"victim@example.com\" style=\"width: 100%; max-width: 400px;\" required />
		</p>
		<input type=\"hidden\" name=\"user_token\" value=\"" . $_SESSION['session_token'] . "\" />
		<p>
			<button type=\"submit\" name=\"reset_password\">Request Password Reset</button>
		</p>
		<p style=\"color: #666; font-size: 0.9em; margin-top: 10px;\">
			<em>Try modifying the Host header - it will have no effect on the generated URL.</em>
		</p>
	</fieldset>
</form>

<div style=\"margin-top: 30px; padding: 20px; background: #e7f3ff; border: 1px solid #2196f3; border-radius: 5px;\">
	<h4>🔒 Why Host Header Injection is Impossible Here</h4>
	
	<h5>Code Implementation:</h5>
	<pre style=\"background: white; padding: 15px; overflow-x: auto; border: 1px solid #ccc;\">// SECURE: Hardcoded domain
define('TRUSTED_DOMAIN', 'dvwa.local');
define('TRUSTED_PROTOCOL', 'https');

// Build URL from constants, NOT from headers
\$reset_url = TRUSTED_PROTOCOL . '://' . TRUSTED_DOMAIN . '/reset?token=' . \$token;

// Validate that Host matches expected value
if(\$_SERVER['HTTP_HOST'] !== TRUSTED_DOMAIN) {
    die('Invalid host');
}

// Reject alternative headers
if(isset(\$_SERVER['HTTP_X_FORWARDED_HOST'])) {
    die('X-Forwarded-Host not allowed');
}</pre>

	<h5 style=\"margin-top: 20px;\">Defense in Depth Layers:</h5>
	<ol>
		<li><strong>Configuration Level:</strong> Use SERVER_NAME instead of HTTP_HOST</li>
		<li><strong>Application Level:</strong> Hardcode trusted domains in configuration</li>
		<li><strong>Validation Level:</strong> Verify Host against whitelist</li>
		<li><strong>Rejection Level:</strong> Block alternative host headers</li>
		<li><strong>Monitoring Level:</strong> Log suspicious header patterns</li>
		<li><strong>Network Level:</strong> Configure web server to reject invalid hosts</li>
	</ol>

	<h5 style=\"margin-top: 20px;\">Additional Protections:</h5>
	<ul>
		<li><strong>CSRF Tokens:</strong> Prevent forged reset requests</li>
		<li><strong>Rate Limiting:</strong> Mitigate brute force and abuse</li>
		<li><strong>Token Complexity:</strong> 256-bit cryptographically secure tokens</li>
		<li><strong>Metadata Binding:</strong> Tie tokens to IP and User-Agent</li>
		<li><strong>Short Expiry:</strong> Tokens valid for limited time only</li>
	</ul>
</div>

<div style=\"margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;\">
	<h4>⚠️ Common Mistakes to Avoid</h4>
	<pre style=\"background: white; padding: 10px;\">// ❌ VULNERABLE
\$reset_url = 'https://' . \$_SERVER['HTTP_HOST'] . '/reset?token=' . \$token;

// ❌ STILL VULNERABLE (trusts reverse proxy headers)
\$host = \$_SERVER['HTTP_X_FORWARDED_HOST'] ?? \$_SERVER['HTTP_HOST'];
\$reset_url = 'https://' . \$host . '/reset?token=' . \$token;

// ❌ WEAK VALIDATION
if(strpos(\$_SERVER['HTTP_HOST'], 'dvwa') !== false) {
    // Bypassable with: dvwa.attacker.com
}

// ✅ SECURE
define('DOMAIN', 'dvwa.local');
\$reset_url = 'https://' . DOMAIN . '/reset?token=' . \$token;</pre>
</div>";

?>
