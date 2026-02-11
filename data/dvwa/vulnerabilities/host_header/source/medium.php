<?php

$hostHeaderHtml = "";

// Medium: Basic validation but still vulnerable
if( isset( $_POST['reset_password'] ) ) {
	$email = $_POST['email'];
	$host = $_SERVER['HTTP_HOST'];
	
	// Basic validation: Check if host contains expected domain
	if(strpos($host, 'dvwa') !== false || strpos($host, 'localhost') !== false) {
		$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
		$token = bin2hex(random_bytes(16));
		$reset_url = $protocol . '://' . $host . '/vulnerabilities/host_header/reset.php?token=' . $token;
		
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<h3>Password Reset Email Sent!</h3>";
		$hostHeaderHtml .= "<p>Email to: <strong>" . htmlspecialchars($email) . "</strong></p>";
		$hostHeaderHtml .= "<p><strong>Reset link:</strong> <code>" . htmlspecialchars($reset_url) . "</code></p>";
		$hostHeaderHtml .= "<div style=\"background: #fff3cd; padding: 15px; margin: 15px 0; border: 1px solid #ffc107;\">";
		$hostHeaderHtml .= "<p style=\"color: #856404;\">⚠️ <strong>Still Vulnerable!</strong></p>";
		$hostHeaderHtml .= "<p>The validation only checks if 'dvwa' or 'localhost' appears in the host.</p>";
		$hostHeaderHtml .= "<p><strong>Bypass examples:</strong></p>";
		$hostHeaderHtml .= "<ul>";
		$hostHeaderHtml .= "<li><code>Host: attacker.com#dvwa.local</code></li>";
		$hostHeaderHtml .= "<li><code>Host: dvwa.attacker.com</code></li>";
		$hostHeaderHtml .= "<li><code>Host: attacker.com?dvwa=1</code></li>";
		$hostHeaderHtml .= "</ul>";
		$hostHeaderHtml .= "</div>";
		$hostHeaderHtml .= "</div>";
	} else {
		$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
		$hostHeaderHtml .= "<p style=\"color: red;\">Invalid host header detected: " . htmlspecialchars($host) . "</p>";
		$hostHeaderHtml .= "<p>Must contain 'dvwa' or 'localhost'</p>";
		$hostHeaderHtml .= "</div>";
	}
}

// Check for alternative headers (still vulnerable)
$alternative_hosts = [];
if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
	$alternative_hosts[] = "X-Forwarded-Host: " . $_SERVER['HTTP_X_FORWARDED_HOST'];
}
if(isset($_SERVER['HTTP_X_HOST'])) {
	$alternative_hosts[] = "X-Host: " . $_SERVER['HTTP_X_HOST'];
}
if(isset($_SERVER['HTTP_X_FORWARDED_SERVER'])) {
	$alternative_hosts[] = "X-Forwarded-Server: " . $_SERVER['HTTP_X_FORWARDED_SERVER'];
}

if(!empty($alternative_hosts)) {
	$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
	$hostHeaderHtml .= "<h4>Alternative Host Headers Detected:</h4>";
	$hostHeaderHtml .= "<ul>";
	foreach($alternative_hosts as $alt) {
		$hostHeaderHtml .= "<li><code>" . htmlspecialchars($alt) . "</code></li>";
	}
	$hostHeaderHtml .= "</ul>";
	$hostHeaderHtml .= "<p style=\"color: orange;\">⚠️ Application may trust these headers in some contexts!</p>";
	$hostHeaderHtml .= "</div>";
}

$hostHeaderHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset style=\"max-width: 600px;\">
		<legend>Password Reset (Medium Security)</legend>
		<p>Basic host validation applied (but bypassable).</p>
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
