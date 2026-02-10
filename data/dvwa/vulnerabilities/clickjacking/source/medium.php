<?php

$clickjackingHtml = "";

// Basic protection using X-Frame-Options: SAMEORIGIN
// Allows framing only from same origin
// This is better but can still be bypassed in some scenarios

header("X-Frame-Options: SAMEORIGIN");

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
		$clickjackingHtml .= "<p style=\"color: orange;\"><em>ℹ️ Protected with X-Frame-Options: SAMEORIGIN</em></p>";
		$clickjackingHtml .= "<p><em>Note: Only prevents framing from external sites. Can still be framed by same origin.</em></p>";
		$clickjackingHtml .= "</div>";
	} else {
		$clickjackingHtml .= "<div class=\"vulnerable_code_area\">";
		$clickjackingHtml .= "<p>No settings were changed.</p>";
		$clickjackingHtml .= "</div>";
	}
}

?>
