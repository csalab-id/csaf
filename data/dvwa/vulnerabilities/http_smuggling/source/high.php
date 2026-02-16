<?php

$smugglingHtml = "";

if( isset( $_POST['test_request'] ) ) {
	$request_data = $_POST['request_data'];
	
	$lines = explode("\n", $request_data);
	$headers_info = [];
	$validation_failed = false;
	
	$has_cl = false;
	$has_te = false;
	$cl_count = 0;
	$te_count = 0;
	$cl_value = 0;
	$te_value = "";
	
	foreach($lines as $line) {
		$line = trim($line);
		
		if(stripos($line, 'Content-Length:') === 0) {
			$cl_count++;
			$has_cl = true;
			$cl_value = trim(substr($line, 15));

			if(!ctype_digit($cl_value)) {
				$validation_failed = true;
				$headers_info[] = "Invalid Content-Length: $cl_value (must be numeric)";
			} else {
				$headers_info[] = "Found Content-Length: $cl_value";
			}
		}
		
		if(stripos($line, 'Transfer-Encoding:') === 0) {
			$te_count++;
			$has_te = true;
			$te_value = trim(substr($line, 18));

			$valid_encodings = ['chunked', 'compress', 'deflate', 'gzip', 'identity'];
			$encodings = array_map('trim', explode(',', strtolower($te_value)));
			$invalid = false;
			foreach($encodings as $enc) {
				if(!in_array($enc, $valid_encodings)) {
					$invalid = true;
					break;
				}
			}
			
			if($invalid) {
				$validation_failed = true;
				$headers_info[] = "Invalid Transfer-Encoding: $te_value";
			} else {
				$headers_info[] = "Found Transfer-Encoding: $te_value";
			}
		}
	}

	if($cl_count > 1) {
		$validation_failed = true;
		$headers_info[] = "Multiple Content-Length headers detected";
	}
	
	if($te_count > 1) {
		$validation_failed = true;
		$headers_info[] = "Multiple Transfer-Encoding headers detected";
	}
	
	$smugglingHtml .= "<div class=\"vulnerable_code_area\">";
	$smugglingHtml .= "<h3>Request Analysis (High Security)</h3>";

	if(($has_cl && $has_te) || $validation_failed) {
		$smugglingHtml .= "<div style=\"background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px; margin: 10px 0;\">";
		$smugglingHtml .= "<p style=\"color: red;\"><strong>❌ Request BLOCKED - Security violations detected</strong></p>";
		$smugglingHtml .= "<ul>";
		foreach($headers_info as $info) {
			$smugglingHtml .= "<li>" . htmlspecialchars($info) . "</li>";
		}
		$smugglingHtml .= "</ul>";
		$smugglingHtml .= "<p>✓ Request smuggling attempt blocked successfully.</p>";
		$smugglingHtml .= "</div>";
	} else if($has_cl) {
		$smugglingHtml .= "<p style=\"color: green;\">✓ Request validated - Content-Length: $cl_value bytes</p>";
	} else if($has_te) {
		$smugglingHtml .= "<p style=\"color: green;\">✓ Request validated - Transfer-Encoding: $te_value</p>";
	}
	
	$smugglingHtml .= "<h4>Your Raw Request:</h4>";
	$smugglingHtml .= "<pre style=\"background: #f5f5f5; padding: 10px; border: 1px solid #ccc;\">" . htmlspecialchars($request_data) . "</pre>";
	$smugglingHtml .= "</div>";
}

$smugglingHtml .= "
<div class=\"vulnerable_code_area\">
<form method=\"POST\">
	<fieldset>
		<p>Enter a raw HTTP request:</p>
		<textarea name=\"request_data\" rows=\"15\" cols=\"80\" style=\"font-family: monospace; width: 100%; max-width: 800px;\">POST /api/process HTTP/1.1
Host: vulnerable-site.com
Content-Type: application/x-www-form-urlencoded
Content-Length: 46
Transfer-Encoding: chunked

0

GET /admin HTTP/1.1
Host: vulnerable-site.com

</textarea>
		<p>
			<button type=\"submit\" name=\"test_request\">Analyze Request</button>
		</p>
	</fieldset>
</form>
</div>";

?>
