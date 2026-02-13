<?php

$hostHeaderHtml = "";

// IMPOSSIBLE: Hardcoded domains + comprehensive validation
$TRUSTED_DOMAINS = [
	'dvwa.lab',
	'dvwa-monitor.lab',
	'dvwa-bunkerweb.lab',
	'dvwa-modsecurity.lab',
	'dvwa.csalab.app',
	'dvwa-aawaf.csalab.app',
	'dvwa-bunkerweb.csalab.app',
	'dvwa-openappsec.csalab.app',
	'dvwa-safeline.csalab.app'
];
$TRUSTED_PROTOCOLS = ['http', 'https'];

if( isset( $_POST['reset_password'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	$email = $_POST['email'];
	$host = $_SERVER['HTTP_HOST'];
	
	// Remove port
	$host_without_port = preg_replace('/:\d+$/', '', $host);
	
	// Multiple validation layers
	$validation_errors = [];
	$matched_domain = null;
	
	// 1. Host header validation
	$host_valid = false;
	foreach($TRUSTED_DOMAINS as $trusted_domain) {
		if(strtolower($host_without_port) === strtolower($trusted_domain)) {
			$host_valid = true;
			$matched_domain = $trusted_domain;
			break;
		}
	}
	if(!$host_valid) {
		$validation_errors[] = "Host header mismatch (expected one of: " . implode(', ', $TRUSTED_DOMAINS) . ", got: " . htmlspecialchars($host_without_port) . ")";
	}
	
	// 2. SERVER_NAME validation
	$servername_valid = false;
	foreach($TRUSTED_DOMAINS as $trusted_domain) {
		if($_SERVER['SERVER_NAME'] === $trusted_domain) {
			$servername_valid = true;
			break;
		}
	}
	if(!$servername_valid) {
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
		// Detect protocol from request
		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
		$reset_url = $protocol . '://' . $matched_domain . '/vulnerabilities/host_header/reset.php?token=' . $token;
		
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
		$hostHeaderHtml .= "<h3>Password Reset Email Sent!</h3>";
		$hostHeaderHtml .= "<p>Email would be sent to: <strong>" . htmlspecialchars($email) . "</strong></p>";
		$hostHeaderHtml .= "<p><strong>Reset link generated:</strong></p>";
		$hostHeaderHtml .= "<code style=\"background: white; padding: 10px; display: block; word-wrap: break-word;\">" . htmlspecialchars($reset_url) . "</code>";
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
<div class=\"vulnerable_code_area\">
	<form method=\"POST\">
		<p>Enter your email to receive a password reset link:</p>
		<p>
			<input type=\"email\" name=\"email\" value=\"victim@example.com\" style=\"width: 100%; max-width: 400px;\" required />
		</p>
		<input type=\"hidden\" name=\"user_token\" value=\"" . $_SESSION['session_token'] . "\" />
		<p>
			<button type=\"submit\" name=\"reset_password\">Request Password Reset</button>
		</p>
	</form>
</div>";

?>
