<?php

$xxeHtml = "";

if( isset( $_POST[ 'submit' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$xml = $_POST[ 'xml' ];

	if( !empty( $xml ) ) {
		// SECURE IMPLEMENTATION
		// Properly disable all external entity processing
		
		// Disable external entity loading (deprecated in PHP 8.0 but still good practice)
		libxml_disable_entity_loader( true );
		
		// Use libxml_use_internal_errors to suppress warnings
		libxml_use_internal_errors( true );
		
		$dom = new DOMDocument();
		
		// Load XML WITHOUT these dangerous flags:
		// - LIBXML_NOENT (substitutes entities)
		// - LIBXML_DTDLOAD (loads external DTD)
		// Use safe flags only
		$loaded = @$dom->loadXML( $xml, LIBXML_NONET | LIBXML_NOCDATA );
		
		if( !$loaded ) {
			$xxeHtml .= "<pre>Error: Invalid XML format.</pre>";
			// Clear errors
			libxml_clear_errors();
		} else {
			// Additional validation: check for DOCTYPE declarations
			if( preg_match( '/<!DOCTYPE/i', $xml ) ) {
				$xxeHtml .= "<pre>Error: DOCTYPE declarations are not allowed for security reasons.</pre>";
			} else {
				$user = $dom->getElementsByTagName( 'user' )->item(0);
				
				if( $user ) {
					$xxeHtml .= "<div class=\"vulnerable_code_area\">";
					$xxeHtml .= "<h2>Parsed XML Data:</h2>";
					$xxeHtml .= "<pre>";
					
					foreach( $user->childNodes as $child ) {
						if( $child->nodeType === XML_ELEMENT_NODE ) {
							$xxeHtml .= htmlspecialchars( $child->nodeName ) . ": " . htmlspecialchars( $child->nodeValue ) . "\n";
						}
					}
					
					$xxeHtml .= "</pre>";
					$xxeHtml .= "</div>";
					$xxeHtml .= "<div class=\"info\">Note: This implementation properly disables XXE by:</div>";
					$xxeHtml .= "<ul>";
					$xxeHtml .= "<li>Using CSRF token protection</li>";
					$xxeHtml .= "<li>Disabling external entity loader</li>";
					$xxeHtml .= "<li>Using LIBXML_NONET flag (disables network access)</li>";
					$xxeHtml .= "<li>Not using LIBXML_NOENT or LIBXML_DTDLOAD</li>";
					$xxeHtml .= "<li>Blocking DOCTYPE declarations</li>";
					$xxeHtml .= "</ul>";
				} else {
					$xxeHtml .= "<pre>Error: Could not find 'user' element in XML.</pre>";
				}
			}
		}
	} else {
		$xxeHtml .= "<pre>Please enter XML data.</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
