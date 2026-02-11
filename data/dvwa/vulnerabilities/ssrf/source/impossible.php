<?php

$html = "";
$url = "";

if( isset( $_POST[ 'Submit' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$url = $_POST[ 'url' ];

	if( !empty( $url ) ) {
		// Whitelist approach - only allow specific domains
		$allowed_domains = array(
			'www.dvwa.co.uk',
			'dvwa.co.uk',
			'www.google.com',
			'google.com'
		);
		
		$parsed_url = parse_url( $url );
		
		// Validate URL structure
		if( $parsed_url === false || !isset( $parsed_url['scheme'] ) || !isset( $parsed_url['host'] ) ) {
			$html .= "<pre>Invalid URL format</pre>";
		} else {
			$scheme = $parsed_url['scheme'];
			$host = strtolower( $parsed_url['host'] );
			
			// Only allow http and https
			if( !in_array( $scheme, array( 'http', 'https' ) ) ) {
				$html .= "<pre>Only HTTP and HTTPS protocols are allowed</pre>";
			} else if( !in_array( $host, $allowed_domains ) ) {
				$html .= "<pre>Domain not in whitelist. Allowed domains: " . implode( ', ', $allowed_domains ) . "</pre>";
			} else {
				// Additional check - resolve DNS and verify it's not a private IP
				$ip = gethostbyname( $host );
				
				if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
					$html .= "<pre>Domain resolves to a private or reserved IP address</pre>";
				} else {
					// Safe to fetch
					$context = stream_context_create( array(
						'http' => array(
							'timeout' => 5,
							'follow_location' => 0,
							'max_redirects' => 0
						)
					));
					
					$response = @file_get_contents( $url, false, $context );
					
					if( $response !== false ) {
						$html .= "<pre>";
						$html .= htmlspecialchars( substr( $response, 0, 1000 ) ); // Limit output
						if( strlen( $response ) > 1000 ) {
							$html .= "\n\n... (truncated)";
						}
						$html .= "</pre>";
					} else {
						$html .= "<pre>Failed to fetch URL: {$url}</pre>";
					}
				}
			}
		}
	} else {
		$html .= "<pre>Please enter a URL</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
