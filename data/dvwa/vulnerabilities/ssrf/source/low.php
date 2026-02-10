<?php

$html = "";
$url = "";

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$url = $_GET[ 'url' ];

	if( !empty( $url ) ) {
		// No validation - direct SSRF vulnerability
		$response = @file_get_contents( $url );
		
		if( $response !== false ) {
			$html .= "<pre>Response from {$url}:\n\n";
			$html .= htmlspecialchars( $response );
			$html .= "</pre>";
		} else {
			$html .= "<pre>Failed to fetch URL: {$url}</pre>";
		}
	} else {
		$html .= "<pre>Please enter a URL</pre>";
	}
}

?>
