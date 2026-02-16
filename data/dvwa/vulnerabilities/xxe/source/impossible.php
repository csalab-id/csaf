<?php

$xxeHtml = "";

if( isset( $_POST[ 'submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$xml = $_POST[ 'xml' ];

	if( !empty( $xml ) ) {
		libxml_disable_entity_loader( true );
		libxml_use_internal_errors( true );
		
		$dom = new DOMDocument();

		$loaded = @$dom->loadXML( $xml, LIBXML_NONET | LIBXML_NOCDATA );
		
		if( !$loaded ) {
			$xxeHtml .= "<pre>Error: Invalid XML format.</pre>";
			libxml_clear_errors();
		} else {
			if( preg_match( '/<!DOCTYPE/i', $xml ) ) {
				$xxeHtml .= "<pre>Blocked! DOCTYPE declarations are not allowed.</pre>";
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
				} else {
					$xxeHtml .= "<div class=\"vulnerable_code_area\">";
					$xxeHtml .= "<h2>Parsed XML Data:</h2>";
					$xxeHtml .= "<pre>" . htmlspecialchars( $dom->textContent ) . "</pre>";
					$xxeHtml .= "</div>";
				}
			}
		}
	} else {
		$xxeHtml .= "<pre>Please enter XML data.</pre>";
	}
}

generateSessionToken();

?>
