<?php

$hostHeaderHtml = "";

// High: Uses whitelist validation
$allowed_hosts = [
	'localhost',
	'127.0.0.1',
	'dvwa',
	'dvwa.lab',
	'dvwa-monitor.lab',
	'dvwa-bunkerweb.lab',
	'dvwa-modsecurity.lab',
	'dvwa.csalab.app',
	'dvwa-aawaf.csalab.app',
	'dvwa-bunkerweb.csalab.app',
	'dvwa-openappsec.csalab.app',
	'dvwa-safeline.csalab.app',
];

if( isset( $_POST['reset_password'] ) ) {
	$email = $_POST['email'];
	$host = $_SERVER['HTTP_HOST'];
	
	// Remove port if present
	$host_without_port = preg_replace('/:\d+$/', '', $host);
	
	// Whitelist validation
	if(in_array(strtolower($host_without_port), $allowed_hosts, true)) {
		$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
		$token = bin2hex(random_bytes(16));
		$reset_url = $protocol . '://' . $host . '/vulnerabilities/host_header/reset.php?token=' . $token;
		
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<h3 style=\"color: green;\">✓ Password Reset Email Sent!</h3>";
		$hostHeaderHtml .= "<p>Email to: <strong>" . htmlspecialchars($email) . "</strong></p>";
		$hostHeaderHtml .= "<p><strong>Reset link:</strong> <code>" . htmlspecialchars($reset_url) . "</code></p>";
		$hostHeaderHtml .= "<div style=\"background: #d4edda; padding: 15px; margin: 15px 0; border: 1px solid #28a745;\">";
		$hostHeaderHtml .= "<p style=\"color: #28a745;\"><strong>✓ Good Protection</strong></p>";
		$hostHeaderHtml .= "<p>Host header validated against whitelist:</p>";
		$hostHeaderHtml .= "<ul>";
		foreach($allowed_hosts as $ah) {
			$hostHeaderHtml .= "<li><code>" . htmlspecialchars($ah) . "</code></li>";
		}
		$hostHeaderHtml .= "</ul>";
		$hostHeaderHtml .= "<p>This prevents most Host header injection attacks.</p>";
		$hostHeaderHtml .= "</div>";
		$hostHeaderHtml .= "</div>";
	} else {
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<h4 style=\"color: red;\">❌ Request Rejected</h4>";
		$hostHeaderHtml .= "<p><strong>Reason:</strong> Host header not in whitelist</p>";
		$hostHeaderHtml .= "<p>Received: <code>" . htmlspecialchars($host) . "</code></p>";
		$hostHeaderHtml .= "<p>Allowed hosts:</p>";
		$hostHeaderHtml .= "<ul>";
		foreach($allowed_hosts as $ah) {
			$hostHeaderHtml .= "<li><code>" . htmlspecialchars($ah) . "</code></li>";
		}
		$hostHeaderHtml .= "</ul>";
		$hostHeaderHtml .= "</div>";
	}
}

$hostHeaderHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset style=\"max-width: 600px;\">
		<legend>Password Reset (High Security)</legend>
		<p>Whitelist-based host validation.</p>
		<p>
			<label>Email:</label><br>
			<input type=\"email\" name=\"email\" value=\"victim@example.com\" style=\"width: 100%; max-width: 400px;\" required />
		</p>
		<p>
			<button type=\"submit\" name=\"reset_password\">Request Password Reset</button>
		</p>
	</fieldset>
</form>";

?>
