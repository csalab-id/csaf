<?php

$hostHeaderHtml = "";

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

	$host_without_port = preg_replace('/:\d+$/', '', $host);

	$validation_passed = true;
	$matched_domain = null;

	$host_valid = false;
	foreach($TRUSTED_DOMAINS as $trusted_domain) {
		if(strtolower($host_without_port) === strtolower($trusted_domain)) {
			$host_valid = true;
			$matched_domain = $trusted_domain;
			break;
		}
	}
	if(!$host_valid) {
		$validation_passed = false;
	}

	$servername_valid = false;
	foreach($TRUSTED_DOMAINS as $trusted_domain) {
		if($_SERVER['SERVER_NAME'] === $trusted_domain) {
			$servername_valid = true;
			break;
		}
	}
	if(!$servername_valid) {
		$validation_passed = false;
	}

	$dangerous_headers = [
		'HTTP_X_FORWARDED_HOST',
		'HTTP_X_HOST',
		'HTTP_X_FORWARDED_SERVER',
		'HTTP_FORWARDED'
	];
	
	foreach($dangerous_headers as $header) {
		if(isset($_SERVER[$header])) {
			$validation_passed = false;
		}
	}

	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$validation_passed = false;
	}

	$rate_limit_key = 'reset_' . session_id();
	if(isset($_SESSION[$rate_limit_key]) && time() - $_SESSION[$rate_limit_key] < 60) {
		$validation_passed = false;
	}
	
	if($validation_passed) {
		$token = bin2hex(random_bytes(32));
		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
		$reset_url = $protocol . '://' . $matched_domain . '/vulnerabilities/host_header/reset.php?token=' . $token;

		$_SESSION['reset_tokens'][$token] = [
			'email' => $email,
			'created' => time(),
			'ip' => $_SERVER['REMOTE_ADDR'],
			'user_agent' => $_SERVER['HTTP_USER_AGENT']
		];

		$_SESSION[$rate_limit_key] = time();
		
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<h3>Password Reset Email Sent!</h3>";
		$hostHeaderHtml .= "<p>Email would be sent to: <strong>" . htmlspecialchars($email) . "</strong></p>";
		$hostHeaderHtml .= "<p><strong>Reset link generated:</strong></p>";
		$hostHeaderHtml .= "<code style=\"background: white; padding: 10px; display: block; word-wrap: break-word;\">" . htmlspecialchars($reset_url) . "</code>";
		$hostHeaderHtml .= "</div>";
		
	} else {
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<p style=\"color: red;\">Request rejected due to security violations</p>";
		$hostHeaderHtml .= "<p>This request has been logged for security monitoring.</p>";
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
