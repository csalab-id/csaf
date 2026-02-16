<?php

$sstiHtml = "";

if( isset( $_GET[ 'submit' ] ) ) {
	$name = $_GET[ 'name' ];

	if( !empty( $name ) ) {
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
	} else {
		$sstiHtml .= "<pre>Please enter your name.</pre>";
	}
}

?>
