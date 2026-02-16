<?php

$sstiHtml = "";

if( isset( $_POST[ 'submit' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$name = $_POST[ 'name' ];

	if( !empty( $name ) ) {
		if( !preg_match('/^[a-zA-Z0-9\s\.\-\']+$/', $name) ) {
			$sstiHtml .= "<pre>Error: Name can only contain letters, numbers, spaces, and basic punctuation (. - ').</pre>";
		} else {
			require_once DVWA_WEB_PAGE_TO_ROOT . 'vulnerabilities/ssti/vendor/twig/twig/lib/Twig/Autoloader.php';
			Twig_Autoloader::register();
			
			try {
				$loader = new Twig_Loader_Array(array(
					'greeting' => 'Hello, {{ name }}! Welcome to our site.'
				));
				$twig = new Twig_Environment($loader, array(
					'autoescape' => 'html',
					'strict_variables' => true
				));
				$result = $twig->render('greeting', array(
					'name' => $name
				));
				
				$sstiHtml .= "<div class=\"vulnerable_code_area\">";
				$sstiHtml .= "<h2>Generated Greeting:</h2>";
				$sstiHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
				$sstiHtml .= $result;
				$sstiHtml .= "</div>";
				$sstiHtml .= "</div>";
				
			} catch (Exception $e) {
				$sstiHtml .= "<pre>ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
			}
		}
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

generateSessionToken();

?>
