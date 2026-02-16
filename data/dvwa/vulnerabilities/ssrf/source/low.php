<?php

$html = "";
$url = "";

if( isset( $_GET[ 'Submit' ] ) ) {
	$url = $_GET[ 'url' ];

	if( !empty( $url ) ) {
		$response = @file_get_contents( $url );
		
		if( $response !== false ) {
			$html .= "<pre>";
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
