<?php

$deserializeHtml = "";

if( isset( $_POST[ 'save' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$theme = $_POST[ 'theme' ];
	$language = $_POST[ 'language' ];

	$prefs = array(
		'theme' => $theme,
		'language' => $language
	);

	$json = json_encode($prefs);
	
	$deserializeHtml .= "<div class=\"vulnerable_code_area\">";
	$deserializeHtml .= "<h3>Preferences Saved!</h3>";
	$deserializeHtml .= "<p><strong>JSON Data:</strong></p>";
	$deserializeHtml .= "<pre>" . htmlspecialchars($json) . "</pre>";
	$deserializeHtml .= "<p><em>Copy this data to load your preferences later.</em></p>";
	$deserializeHtml .= "</div>";
}

if( isset( $_POST[ 'load' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$data = $_POST[ 'data' ];
	
	if( !empty( $data ) ) {
		$prefs = json_decode($data, true);
		
		if (json_last_error() === JSON_ERROR_NONE) {
			if (isset($prefs['theme']) && isset($prefs['language'])) {
				$valid_themes = array('light', 'dark', 'blue');
				$valid_languages = array('en', 'es', 'fr');
				
				if (in_array($prefs['theme'], $valid_themes) && in_array($prefs['language'], $valid_languages)) {
					$deserializeHtml .= "<div class=\"vulnerable_code_area\">";
					$deserializeHtml .= "<h3>Preferences Loaded!</h3>";
					$deserializeHtml .= "<p><strong>Theme:</strong> " . htmlspecialchars($prefs['theme']) . "</p>";
					$deserializeHtml .= "<p><strong>Language:</strong> " . htmlspecialchars($prefs['language']) . "</p>";
					$deserializeHtml .= "</div>";
				} else {
					$deserializeHtml .= "<pre>Error: Invalid theme or language value.</pre>";
				}
			} else {
				$deserializeHtml .= "<pre>Error: Missing required fields.</pre>";
			}
		} else {
			$deserializeHtml .= "<pre>Error: Invalid JSON format.</pre>";
		}
	} else {
		$deserializeHtml .= "<pre>Please provide JSON data.</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
