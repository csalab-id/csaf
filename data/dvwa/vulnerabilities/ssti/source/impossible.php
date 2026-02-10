<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		// SECURE IMPLEMENTATION
		// Never use eval() or dynamic code execution with user input
		// Use proper escaping and static templates
		
		// Validate input - only allow letters, spaces, and basic punctuation
		if( !preg_match('/^[a-zA-Z0-9\s\.\-\']+$/', $name) ) {
			$sstiHtml .= "<pre>Error: Name can only contain letters, numbers, spaces, and basic punctuation (. - ').</pre>";
		} else {
			// Use safe string replacement instead of template evaluation
			// No eval(), no dynamic code execution
			$template = "Hello, {{NAME}}! Welcome to our site.";
			
			// Safe replacement using str_replace
			$greeting = str_replace('{{NAME}}', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), $template);
			
			$sstiHtml .= "<div class=\"vulnerable_code_area\">";
			$sstiHtml .= "<h2>Generated Greeting:</h2>";
			$sstiHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
			$sstiHtml .= $greeting;
			$sstiHtml .= "</div>";
			$sstiHtml .= "</div>";
			$sstiHtml .= "<div class=\"info\">Note: This implementation properly prevents SSTI by:</div>";
			$sstiHtml .= "<ul>";
			$sstiHtml .= "<li>Using CSRF token protection</li>";
			$sstiHtml .= "<li>Never using eval() or dynamic code execution</li>";
			$sstiHtml .= "<li>Using str_replace() instead of template evaluation</li>";
			$sstiHtml .= "<li>Properly escaping output with htmlspecialchars()</li>";
			$sstiHtml .= "<li>Validating input with strict regex</li>";
			$sstiHtml .= "</ul>";
		}
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
