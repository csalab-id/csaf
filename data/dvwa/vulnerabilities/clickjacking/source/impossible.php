<?php

$clickjackingHtml = "";

header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none'");

if( isset( $_POST[ 'submit' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$settings = array();
	
	if( isset( $_POST[ 'public_profile' ] ) ) {
		$settings[] = "Public Profile Enabled";
	}
	if( isset( $_POST[ 'share_data' ] ) ) {
		$settings[] = "Data Sharing Enabled";
	}
	if( isset( $_POST[ 'admin_access' ] ) ) {
		$settings[] = "Admin Access Granted";
	}
	
	if( !empty( $settings ) ) {
		$clickjackingHtml .= "<div class=\"vulnerable_code_area\">";
		$clickjackingHtml .= "<h3>Settings Updated Successfully!</h3>";
		$clickjackingHtml .= "<p><strong>Changes made:</strong></p>";
		$clickjackingHtml .= "<ul>";
		foreach( $settings as $setting ) {
			$clickjackingHtml .= "<li>" . htmlspecialchars($setting) . "</li>";
		}
		$clickjackingHtml .= "</ul>";
		$clickjackingHtml .= "</div>";
	} else {
		$clickjackingHtml .= "<div class=\"vulnerable_code_area\">";
		$clickjackingHtml .= "<p>No settings were changed.</p>";
		$clickjackingHtml .= "</div>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

// Add JavaScript frame-busting code
$clickjackingHtml .= "
<script>
// Frame-busting code - detect if page is in iframe
if (top !== self) {
	// Page is in an iframe, break out
	top.location = self.location;
}

// Alternative: prevent the page from being displayed in iframe
if (window.top !== window.self) {
	document.body.innerHTML = '<h1>Clickjacking Attempt Detected!</h1><p>This page cannot be displayed in a frame for security reasons.</p>';
}
</script>
";

?>
