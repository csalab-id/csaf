<?php

$xxeHtml = "";

if( isset( $_POST[ 'submit' ] ) ) {
	$xml = $_POST[ 'xml' ];

	if( !empty( $xml ) ) {
		$dom = new DOMDocument();
		$dom->loadXML( $xml, LIBXML_NOENT | LIBXML_DTDLOAD );
		
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
	} else {
		$xxeHtml .= "<pre>Please enter XML data.</pre>";
	}
}

?>
