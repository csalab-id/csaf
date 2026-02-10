<?php

$clickjackingHtml = "";

// No X-Frame-Options header - VULNERABLE to clickjacking
// The page can be embedded in any iframe

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
		$clickjackingHtml .= "<p style=\"color: red;\"><em>⚠️ This page has no clickjacking protection!</em></p>";
		$clickjackingHtml .= "</div>";
	} else {
		$clickjackingHtml .= "<div class=\"vulnerable_code_area\">";
		$clickjackingHtml .= "<p>No settings were changed.</p>";
		$clickjackingHtml .= "</div>";
	}
}

?>
