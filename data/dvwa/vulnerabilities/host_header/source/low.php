<?php

$hostHeaderHtml = "";

// VULNERABLE: Directly uses HTTP_HOST without validation
if( isset( $_POST['reset_password'] ) ) {
	$email = $_POST['email'];
	
	// VULNERABLE: Trusts Host header to build reset link
	$host = $_SERVER['HTTP_HOST'];
	$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	
	// Generate token (simplified for demo)
	$token = bin2hex(random_bytes(16));
	
	// Build reset URL using untrusted Host header
	$reset_url = $protocol . '://' . $host . '/vulnerabilities/host_header/reset.php?token=' . $token;
	
	$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
	$hostHeaderHtml .= "<h3>Password Reset Email Sent!</h3>";
	$hostHeaderHtml .= "<p>Email would be sent to: <strong>" . htmlspecialchars($email) . "</strong></p>";
	$hostHeaderHtml .= "<p><strong>Reset link generated:</strong></p>";
	$hostHeaderHtml .= "<code style=\"background: white; padding: 10px; display: block; word-wrap: break-word;\">" . htmlspecialchars($reset_url) . "</code>";
	$hostHeaderHtml .= "</div>";
}

$hostHeaderHtml .= "
<div class=\"vulnerable_code_area\">
	<form method=\"POST\">
		<p>Enter your email to receive a password reset link:</p>
		<p>
			<input type=\"email\" name=\"email\" value=\"victim@example.com\" style=\"width: 100%; max-width: 400px;\" required />
		</p>
		<p>
			<button type=\"submit\" name=\"reset_password\">Request Password Reset</button>
		</p>
	</form>
</div>";

?>
