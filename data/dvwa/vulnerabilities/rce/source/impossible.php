<?php

$rceHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		if( !preg_match('/^[a-zA-Z0-9\s\.\-\']+$/', $name) ) {
			$rceHtml .= "<pre>Error: Name can only contain letters, numbers, spaces, and basic punctuation (. - ').</pre>";
		} else {
			$greeting = "Hello, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "! Welcome to our site.";

			$rceHtml .= "<div class=\"vulnerable_code_area\">";
			$rceHtml .= "<h2>Generated Greeting:</h2>";
			$rceHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
			$rceHtml .= $greeting;
			$rceHtml .= "</div>";
			$rceHtml .= "</div>";
		}
	} else {
		$rceHtml .= "<pre>Please enter your name.</pre>";
	}
}

generateSessionToken();

?>
