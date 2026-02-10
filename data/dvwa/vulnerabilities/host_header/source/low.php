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
	$hostHeaderHtml .= "<div style=\"background: #ffe6e6; padding: 15px; margin: 15px 0; border: 2px solid #ff0000; border-radius: 5px;\">";
	$hostHeaderHtml .= "<h4 style=\"color: #cc0000;\">⚠️ VULNERABILITY DEMONSTRATED</h4>";
	$hostHeaderHtml .= "<p><strong>Reset link generated:</strong></p>";
	$hostHeaderHtml .= "<code style=\"background: white; padding: 10px; display: block; word-wrap: break-word;\">" . htmlspecialchars($reset_url) . "</code>";
	$hostHeaderHtml .= "<p style=\"margin-top: 10px;\"><strong>Problem:</strong> The Host header is controlled by the attacker!</p>";
	$hostHeaderHtml .= "<p>If you sent a request with <code>Host: attacker.com</code>, the reset link would point to attacker's server.</p>";
	$hostHeaderHtml .= "<p><strong>Impact:</strong> Attacker receives the reset token when victim clicks the link.</p>";
	$hostHeaderHtml .= "</div>";
	$hostHeaderHtml .= "</div>";
}

// Demonstrate dynamic content generation based on Host
if( isset( $_GET['show_info'] ) ) {
	$host = $_SERVER['HTTP_HOST'];
	$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	
	$hostHeaderHtml .= "<div class=\"vulnerable_code_area\">";
	$hostHeaderHtml .= "<h3>Dynamic Content Based on Host Header</h3>";
	$hostHeaderHtml .= "<p><strong>Base URL:</strong> <code>" . htmlspecialchars($protocol . '://' . $host) . "</code></p>";
	$hostHeaderHtml .= "<p><strong>API Endpoint:</strong> <code>" . htmlspecialchars($protocol . '://' . $host . '/api/') . "</code></p>";
	$hostHeaderHtml .= "<p><strong>Static Assets:</strong> <code>" . htmlspecialchars($protocol . '://' . $host . '/static/') . "</code></p>";
	$hostHeaderHtml .= "<div style=\"background: #ffe6e6; padding: 10px; margin: 10px 0; border: 1px solid #ffcccc;\">";
	$hostHeaderHtml .= "<p style=\"color: #cc0000;\">⚠️ All these URLs are vulnerable to Host header manipulation!</p>";
	$hostHeaderHtml .= "</div>";
	$hostHeaderHtml .= "</div>";
}

$hostHeaderHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset style=\"max-width: 600px;\">
		<legend>Password Reset Request</legend>
		<p>Enter your email to receive a password reset link:</p>
		<p>
			<label>Email:</label><br>
			<input type=\"email\" name=\"email\" value=\"victim@example.com\" style=\"width: 100%; max-width: 400px;\" required />
		</p>
		<p>
			<button type=\"submit\" name=\"reset_password\">Request Password Reset</button>
		</p>
		<p style=\"color: #666; font-size: 0.9em;\">
			<em>In a real attack, send a request with: <code>Host: attacker.com</code></em>
		</p>
	</fieldset>
</form>

<div style=\"margin-top: 20px;\">
	<a href=\"?show_info=1\" class=\"button\">Show Dynamic Content Example</a>
</div>

<div style=\"margin-top: 30px; padding: 15px; background: #e7f3ff; border: 1px solid #2196f3; border-radius: 5px;\">
	<h4>How to Test</h4>
	<p><strong>Using cURL:</strong></p>
	<pre style=\"background: white; padding: 10px; overflow-x: auto;\">curl -X POST http://dvwa.local/vulnerabilities/host_header/ \\
  -H \"Host: attacker.com\" \\
  -d \"email=victim@example.com&reset_password=Submit\"</pre>
	
	<p style=\"margin-top: 15px;\"><strong>Using Burp Suite:</strong></p>
	<ol>
		<li>Intercept the password reset request</li>
		<li>Modify the Host header to <code>attacker.com</code></li>
		<li>Forward the request</li>
		<li>Observe the reset link contains attacker's domain</li>
	</ol>
	
	<p style=\"margin-top: 15px;\"><strong>Alternative Headers to Test:</strong></p>
	<ul>
		<li><code>X-Forwarded-Host: attacker.com</code></li>
		<li><code>X-Host: attacker.com</code></li>
		<li><code>X-Forwarded-Server: attacker.com</code></li>
		<li><code>Forwarded: host=attacker.com</code></li>
	</ul>
</div>";

?>
