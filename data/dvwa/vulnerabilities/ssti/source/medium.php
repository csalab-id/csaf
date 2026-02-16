<?php

$sstiHtml = "";

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
			'proc_open',
			'<?php',
			'<?='
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
			$template = "Hello, {{name}}! Welcome to our site.";
			$output = str_replace('{{name}}', $name, $template);

			ob_start();
			eval('?>' . $output);
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
