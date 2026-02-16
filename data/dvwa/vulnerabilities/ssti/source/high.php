<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		if( preg_match('/\{\{.*\}\}/', $name) ) {
			$sstiHtml .= "<pre>Blocked! Template syntax not allowed in user input.</pre>";
		} else {
			$blacklist = array(
				'eval', 'assert', 'exec', 'system', 'passthru', 
				'shell_exec', 'popen', 'proc_open', 'pcntl_exec',
				'file_get_contents', 'readfile', 'file_put_contents', 'fopen',
				'<?php', '<?=', '<?', 'include', 'require',
				'$_GET', '$_POST', '$_REQUEST', '$_COOKIE', '$_SERVER',
				'base64_decode', 'gzinflate', 'str_rot13'
			);
			
			$blocked = false;
			foreach( $blacklist as $keyword ) {
				if( stripos( $name, $keyword ) !== false ) {
					$sstiHtml .= "<pre>Blocked! Detected dangerous keyword: " . htmlspecialchars($keyword) . "</pre>";
					$blocked = true;
					break;
				}
			}
			
			if( !$blocked ) {
				$template = "<?php \$user = '{{name}}'; echo \"Hello, \$user! Welcome to our site.\"; ?>";
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
		}
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

?>
