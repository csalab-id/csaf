<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		if( !preg_match('/^[a-zA-Z0-9\s\.\-\']+$/', $name) ) {
			$sstiHtml .= "<pre>Error: Name can only contain letters, numbers, spaces, and basic punctuation (. - ').</pre>";
		} else {
			$template = "Hello, {{NAME}}! Welcome to our site.";

			$greeting = str_replace('{{NAME}}', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), $template);

			$sstiHtml .= "<div class=\"vulnerable_code_area\">";
			$sstiHtml .= "<h2>Generated Greeting:</h2>";
			$sstiHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
			$sstiHtml .= $greeting;
			$sstiHtml .= "</div>";
			$sstiHtml .= "</div>";
		}
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

generateSessionToken();

?>
