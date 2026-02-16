<?php

$rceHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
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
				$rceHtml .= "<pre>Blocked! Detected potentially dangerous function: " . htmlspecialchars($keyword) . "</pre>";
				$blocked = true;
				break;
			}
		}
		
		if( !$blocked ) {
			$code = "echo 'Hello, {$name}! Welcome to our site.';";
			
			ob_start();
			eval($code);
			$result = ob_get_clean();
			
			$rceHtml .= "<div class=\"vulnerable_code_area\">";
			$rceHtml .= "<h2>Generated Greeting:</h2>";
			$rceHtml .= "<div style=\"padding: 10px; background: #f0f0f0; border-radius: 5px;\">";
			$rceHtml .= $result;
			$rceHtml .= "</div>";
			$rceHtml .= "</div>";
		}
	} else {
		$rceHtml .= "<pre>Please enter your name.</pre>";
	}
}

?>
