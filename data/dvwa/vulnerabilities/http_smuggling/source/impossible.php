<?php

$smugglingHtml = "";

if( isset( $_POST['test_request'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	$request_data = $_POST['request_data'];
	
	$lines = explode("\n", $request_data);
	$headers_info = [];
	$errors = [];
	$warnings = [];
	
	$has_cl = false;
	$has_te = false;
	$cl_count = 0;
	$te_count = 0;
	$cl_value = 0;
	$te_values = [];

	foreach($lines as $line_num => $line) {
		$original_line = $line;
		$line = trim($line);

		if(empty($line)) {
			continue;
		}

		if(preg_match('/^\s+/', $original_line)) {
			$errors[] = "Line $line_num: Line folding detected (deprecated in HTTP/1.1)";
		}

		if(strpos($line, "\r") !== false) {
			$errors[] = "Line $line_num: CRLF injection attempt detected";
		}

		if(stripos($line, 'Content-Length:') === 0) {
			$cl_count++;
			$has_cl = true;
			$value = trim(substr($line, 15));

			if(preg_match('/Content-Length\s*:\s+/i', $line) && preg_match('/:\s{2,}/', $line)) {
				$errors[] = "Line $line_num: Suspicious whitespace in Content-Length header";
			}

			if(!ctype_digit($value)) {
				$errors[] = "Line $line_num: Invalid Content-Length value (must be positive integer)";
			} else {
				$cl_value = (int)$value;
				if($cl_value < 0) {
					$errors[] = "Line $line_num: Content-Length cannot be negative";
				}
			}
			
			$headers_info[] = "Content-Length: $value";
		}

		if(stripos($line, 'Transfer-Encoding:') === 0) {
			$te_count++;
			$has_te = true;
			$value = trim(substr($line, 18));
			$te_values[] = $value;

			if(preg_match('/Transfer-Encoding\s*:\s+/i', $line) && preg_match('/:\s{2,}/', $line)) {
				$errors[] = "Line $line_num: Suspicious whitespace in Transfer-Encoding header";
			}

			$valid_encodings = ['chunked', 'compress', 'deflate', 'gzip', 'identity'];
			$encodings = array_map('trim', explode(',', strtolower($value)));
			
			foreach($encodings as $encoding) {
				if(!in_array($encoding, $valid_encodings)) {
					$errors[] = "Line $line_num: Invalid Transfer-Encoding value: $encoding";
				}
			}
			
			$headers_info[] = "Transfer-Encoding: $value";
		}

		if(preg_match('/Transfer-Encoding\s*:\s*[^\r\n]*[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/i', $line)) {
			$errors[] = "Line $line_num: Control characters in Transfer-Encoding header";
		}
		
		if(preg_match('/Content-Length\s*:\s*[^\r\n]*[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/i', $line)) {
			$errors[] = "Line $line_num: Control characters in Content-Length header";
		}

		if((stripos($line, 'Content-Length') !== false || stripos($line, 'Transfer-Encoding') !== false) && strpos($line, "\t") !== false) {
			$errors[] = "Line $line_num: Tab character detected in header (potential obfuscation)";
		}
	}

	if($cl_count > 1) {
		$errors[] = "Multiple Content-Length headers (RFC 7230 violation)";
	}

	if($te_count > 1) {
		$errors[] = "Multiple Transfer-Encoding headers (RFC 7230 violation)";
	}

	if($has_cl && $has_te) {
		$errors[] = "CRITICAL: Both Content-Length and Transfer-Encoding present (MUST reject per RFC 7230 §3.3.3)";
	}
	
	$smugglingHtml .= "<div class=\"vulnerable_code_area\">";
	$smugglingHtml .= "<h3>Request Analysis (Impossible Security)</h3>";

	if(!empty($errors)) {
		$smugglingHtml .= "<div style=\"background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px; margin: 10px 0;\">";
		$smugglingHtml .= "<p style=\"color: red;\"><strong>❌ Request REJECTED - Security violations detected</strong></p>";
		$smugglingHtml .= "<p>This request has been logged and blocked. All RFC compliance checks enforced.</p>";
		$smugglingHtml .= "</div>";
		
	} else {
		$smugglingHtml .= "<p style=\"color: green;\">✓ Request validated - All security checks passed</p>";
		
		if(!empty($headers_info)) {
			foreach($headers_info as $info) {
				$smugglingHtml .= "<p>" . htmlspecialchars($info) . "</p>";
			}
		}
	}
	
	$smugglingHtml .= "<h4>Your Raw Request:</h4>";
	$smugglingHtml .= "<pre style=\"background: #f5f5f5; padding: 10px; border: 1px solid #ccc;\">" . htmlspecialchars($request_data) . "</pre>";
	
	$smugglingHtml .= "</div>";
}

generateSessionToken();

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
		<input type=\"hidden\" name=\"user_token\" value=\"" . $_SESSION['session_token'] . "\" />
		<p>
			<button type=\"submit\" name=\"test_request\">Analyze Request</button>
		</p>

	</fieldset>
</form>
</div>";

?>
