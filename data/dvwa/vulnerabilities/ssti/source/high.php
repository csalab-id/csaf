<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	// Get input
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		// More strict filtering - only allow alphanumeric and basic punctuation
		// But still vulnerable to advanced bypasses
		
		// Remove special characters that could be used for code injection
		if( preg_match('/[{}()<>$`\[\]|&;]/', $name) ) {
			$sstiHtml .= "<pre>Blocked! Input contains potentially dangerous characters.</pre>";
		} else {
			// Still uses eval which is dangerous
			// Can be bypassed with PHP wrapper functions or obfuscation
			$template = "Hello, {$name}! Welcome to our site.";
			
			// Even with filtering, eval is inherently dangerous
			ob_start();
			eval('?>' . $template);
			$result = ob_get_clean();
			
			$sstiHtml .= "<div class=\"vulnerable_code_area\">";
			$sstiHtml .= "<h2>Generated Greeting:</h2>";
			$sstiHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
			$sstiHtml .= $result;
			$sstiHtml .= "</div>";
			$sstiHtml .= "</div>";
		}
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

?>
