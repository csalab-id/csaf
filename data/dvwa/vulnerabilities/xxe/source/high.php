<?php

$xxeHtml = "";

if( isset( $_POST[ 'submit' ] ) ) {
	// Get input
	$xml = $_POST[ 'xml' ];

	if( !empty( $xml ) ) {
		// Attempt to disable external entities
		// But implementation is flawed
		
		$dom = new DOMDocument();
		
		// Disable substitution of entities but DTD still loaded
		// This is vulnerable to XXE OOB (Out-of-Band) attacks
		libxml_disable_entity_loader( true );
		
		// Still allows DTD processing which can be exploited
		@$dom->loadXML( $xml, LIBXML_DTDLOAD );
		
		if( !$dom ) {
			$xxeHtml .= "<pre>Error parsing XML.</pre>";
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
	} else {
		$xxeHtml .= "<pre>Please enter XML data.</pre>";
	}
}

?>
