<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	// Get input
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		// Basic blacklist approach - blocks common dangerous functions
		// This is NOT secure and can be bypassed
		$blacklist = array(
			'eval',
			'exec',
			'system',
			'passthru',
			'shell_exec',
			'phpinfo',
			'popen',
			'proc_open'
		);
		
		$blocked = false;
		foreach( $blacklist as $keyword ) {
			if( stripos( $name, $keyword ) !== false ) {
				$sstiHtml .= "<pre>Blocked! Detected potentially dangerous function: " . htmlspecialchars($keyword) . "</pre>";
				$blocked = true;
				break;
			}
		}
		
		if( !$blocked ) {
			// Still vulnerable - can use backticks, file operations, etc.
			$template = "Hello, {$name}! Welcome to our site.";
			
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
