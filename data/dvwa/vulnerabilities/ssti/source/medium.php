<?php

$sstiHtml = "";

if( isset( $_POST[ 'submit' ] ) ) {
	$name = $_POST[ 'name' ];

	if( !empty( $name ) ) {
		$blacklist = array(
			'_self',
			'env',
			'app',
			'map',
			'filter',
			'reduce'
		);
		
		$blocked = false;
		foreach( $blacklist as $keyword ) {
			if( stripos( $name, $keyword ) !== false ) {
				$sstiHtml .= "<pre>Blocked! Detected potentially dangerous keyword: " . htmlspecialchars($keyword) . "</pre>";
				$blocked = true;
				break;
			}
		}
		
		if( !$blocked ) {
			require_once DVWA_WEB_PAGE_TO_ROOT . 'vulnerabilities/ssti/vendor/twig/twig/lib/Twig/Autoloader.php';
			Twig_Autoloader::register();
			
			try {
				$loader = new Twig_Loader_String();
				$twig = new Twig_Environment($loader);
				$result = $twig->render($name);
				
				$sstiHtml .= "<div class=\"vulnerable_code_area\">";
				$sstiHtml .= "<h2>Generated Greeting:</h2>";
				$sstiHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
				$sstiHtml .= "Hello, " . $result . "! Welcome to our site.";
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

?>
