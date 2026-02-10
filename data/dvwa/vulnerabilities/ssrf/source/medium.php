<?php

$html = "";
$url = "";

if( isset( $_GET[ 'Submit' ] ) ) {
	// Get input
	$url = $_GET[ 'url' ];

	if( !empty( $url ) ) {
		// Basic blacklist - can be bypassed
		$blocked = array( 'localhost', '127.0.0.1', '0.0.0.0' );
		
		$is_blocked = false;
		foreach( $blocked as $block ) {
			if( stripos( $url, $block ) !== false ) {
				$is_blocked = true;
				break;
			}
		}
		
		if( $is_blocked ) {
			$html .= "<pre>URL is blocked for security reasons</pre>";
		} else {
			$response = @file_get_contents( $url );
			
			if( $response !== false ) {
				$html .= "<pre>Response from {$url}:\n\n";
				$html .= htmlspecialchars( $response );
				$html .= "</pre>";
			} else {
				$html .= "<pre>Failed to fetch URL: {$url}</pre>";
			}
		}
	} else {
		$html .= "<pre>Please enter a URL</pre>";
	}
}

?>
