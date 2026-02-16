<?php

$html = "";
$url = "";

if( isset( $_POST[ 'Submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$url = $_POST[ 'url' ];

	if( !empty( $url ) ) {
		$allowed_domains = array(
			'www.google.com',
			'google.com'
		);
		
		$parsed_url = parse_url( $url );

		if( $parsed_url === false || !isset( $parsed_url['scheme'] ) || !isset( $parsed_url['host'] ) ) {
			$html .= "<pre>Invalid URL format</pre>";
		} else {
			$scheme = $parsed_url['scheme'];
			$host = strtolower( $parsed_url['host'] );

			if( !in_array( $scheme, array( 'http', 'https' ) ) ) {
				$html .= "<pre>Only HTTP and HTTPS protocols are allowed</pre>";
			} else if( !in_array( $host, $allowed_domains ) ) {
				$html .= "<pre>Domain not in whitelist. Allowed domains: " . implode( ', ', $allowed_domains ) . "</pre>";
			} else {
				$ip = gethostbyname( $host );
				$is_safe = true;

				if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
					$html .= "<pre>Domain resolves to a private or reserved IP address</pre>";
					$is_safe = false;
				}

				if( $is_safe ) {
					$dns_records = @dns_get_record( $host, DNS_AAAA );
					if( $dns_records !== false && count( $dns_records ) > 0 ) {
						foreach( $dns_records as $record ) {
							if( isset( $record['ipv6'] ) ) {
								if( filter_var( $record['ipv6'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
									$html .= "<pre>Domain has IPv6 record pointing to private/reserved address</pre>";
									$is_safe = false;
									break;
								}
							}
						}
					}
				}
				
				if( $is_safe ) {
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
						$html .= htmlspecialchars( substr( $response, 0, 1000 ) );
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

generateSessionToken();

?>
