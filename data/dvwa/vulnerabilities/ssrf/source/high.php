<?php

$html = "";
$url = "";

if( isset( $_GET[ 'Submit' ] ) ) {
	$url = $_GET[ 'url' ];

	if( !empty( $url ) ) {
		$blocked_keywords = array(
			'localhost', '127.0.0.1', '0.0.0.0', '[::]',
			'192.168.', '10.', '172.16.', '172.17.', '172.18.',
			'172.19.', '172.20.', '172.21.', '172.22.', '172.23.',
			'172.24.', '172.25.', '172.26.', '172.27.', '172.28.',
			'172.29.', '172.30.', '172.31.',
			'169.254.', 'file://', 'dict://', 'ftp://', 'gopher://'
		);
		
		$is_blocked = false;
		$url_lower = strtolower( $url );
		
		foreach( $blocked_keywords as $keyword ) {
			if( stripos( $url_lower, $keyword ) !== false ) {
				$is_blocked = true;
				break;
			}
		}

		if( !preg_match( '/^https?:\/\//', $url ) ) {
			$is_blocked = true;
		}
		
		if( $is_blocked ) {
			$html .= "<pre>URL is blocked for security reasons</pre>";
		} else {
			$response = @file_get_contents( $url );
			
			if( $response !== false ) {
				$html .= "<pre>";
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
