<?php

$clickjackingHtml = "";

// Better protection using both X-Frame-Options and frame-ancestors CSP
// X-Frame-Options: DENY - prevents all framing
// But still lacks comprehensive CSP

header("X-Frame-Options: DENY");

if( isset( $_POST[ 'submit' ] ) ) {
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
		$clickjackingHtml .= "<p style=\"color: green;\"><em>✓ Protected with X-Frame-Options: DENY</em></p>";
		$clickjackingHtml .= "<p><em>Note: X-Frame-Options is deprecated. Modern browsers prefer CSP frame-ancestors.</em></p>";
		$clickjackingHtml .= "</div>";
	} else {
		$clickjackingHtml .= "<div class=\"vulnerable_code_area\">";
		$clickjackingHtml .= "<p>No settings were changed.</p>";
		$clickjackingHtml .= "</div>";
	}
}

?>
