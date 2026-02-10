<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	// Get input
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		// VULNERABLE: Direct template evaluation with user input
		// Using eval() to demonstrate SSTI
		
		// Template with user input directly injected
		$template = "Hello, {$name}! Welcome to our site.";
		
		// Evaluate template - EXTREMELY DANGEROUS
		// User can inject PHP code like: ${system('whoami')} or ${phpinfo()}
		$greeting = $template;
		
		// Even more dangerous: allow expression evaluation
		// This simulates template engines that evaluate expressions
		ob_start();
		eval('?>' . $greeting);
		$result = ob_get_clean();
		
		$sstiHtml .= "<div class=\"vulnerable_code_area\">";
		$sstiHtml .= "<h2>Generated Greeting:</h2>";
		$sstiHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
		$sstiHtml .= $result;
		$sstiHtml .= "</div>";
		$sstiHtml .= "</div>";
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

?>
