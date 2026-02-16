<?php

$rceHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
		$blacklist = array(
			'eval', 'assert', 'exec', 'system', 'passthru', 
			'shell_exec', 'popen', 'proc_open', 'pcntl_exec',
			'file_get_contents', 'readfile', 'file_put_contents', 'fopen', 'fwrite',
			'<?php', '<?=', '<?', 'include', 'require', 'require_once', 'include_once',
			'$_GET', '$_POST', '$_REQUEST', '$_COOKIE', '$_SERVER', '$_FILES',
			'base64_decode', 'str_rot13', 'gzinflate', 'gzuncompress', 'gzdecode',
			'preg_replace', '/e'
		);
		
		$blocked = false;
		foreach( $blacklist as $keyword ) {
			if( stripos( $name, $keyword ) !== false ) {
				$rceHtml .= "<pre>Blocked! Detected dangerous function: " . htmlspecialchars($keyword) . "</pre>";
				$blocked = true;
				break;
			}
		}

		if( !$blocked && strpos( $name, ';' ) !== false ) {
			$rceHtml .= "<pre>Blocked! Semicolons are not allowed.</pre>";
			$blocked = true;
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
