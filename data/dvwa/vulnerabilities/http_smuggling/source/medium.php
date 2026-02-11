<?php

$smugglingHtml = "";

// Medium: Basic validation but incomplete
if( isset( $_POST['test_request'] ) ) {
	$request_data = $_POST['request_data'];
	
	$lines = explode("\n", $request_data);
	$headers_info = [];
	
	$has_cl = false;
	$has_te = false;
	$cl_value = 0;
	$te_value = "";
	
	foreach($lines as $line) {
		$line = trim($line);
		if(stripos($line, 'Content-Length:') === 0) {
			$has_cl = true;
			$cl_value = trim(substr($line, 15));
			$headers_info[] = "Found Content-Length: $cl_value";
		}
		if(stripos($line, 'Transfer-Encoding:') === 0) {
			$has_te = true;
			$te_value = trim(substr($line, 18));
			$headers_info[] = "Found Transfer-Encoding: $te_value";
		}
	}
	
	$smugglingHtml .= "<div class=\"vulnerable_code_area\">";
	$smugglingHtml .= "<h3>Request Analysis (Medium Security)</h3>";
	
	// Basic check for conflicting headers
	if($has_cl && $has_te) {
		$smugglingHtml .= "<div style=\"background: #fff3cd; padding: 15px; border: 2px solid #ffc107; border-radius: 5px; margin: 10px 0;\">";
		$smugglingHtml .= "<h4 style=\"color: #856404;\">⚠️ WARNING: Conflicting Headers Detected!</h4>";
		$smugglingHtml .= "<p>Both Content-Length and Transfer-Encoding headers found.</p>";
		$smugglingHtml .= "<p style=\"color: orange;\"><strong>Protection Applied:</strong> Logging the conflict (but still processing...)</p>";
		$smugglingHtml .= "<ul>";
		foreach($headers_info as $info) {
			$smugglingHtml .= "<li>" . htmlspecialchars($info) . "</li>";
		}
		$smugglingHtml .= "</ul>";
		$smugglingHtml .= "<p><em>Note: In a real scenario, this should be rejected, but medium level only logs it.</em></p>";
		$smugglingHtml .= "</div>";
		
		// Still vulnerable - processes the request
		$smugglingHtml .= "<div style=\"background: #ffe6e6; padding: 10px; margin: 10px 0;\">";
		$smugglingHtml .= "<p><strong>Still Vulnerable:</strong> Request is processed despite conflicting headers!</p>";
		$smugglingHtml .= "<p>Attacker can still exploit parsing differences between servers.</p>";
		$smugglingHtml .= "</div>";
	} else if($has_cl) {
		$smugglingHtml .= "<p style=\"color: green;\">✓ Only Content-Length header found: $cl_value</p>";
	} else if($has_te) {
		$smugglingHtml .= "<p style=\"color: green;\">✓ Only Transfer-Encoding header found: $te_value</p>";
	}
	
	$smugglingHtml .= "<h4>Your Request:</h4>";
	$smugglingHtml .= "<pre style=\"background: #f5f5f5; padding: 10px; border: 1px solid #ccc;\">" . htmlspecialchars($request_data) . "</pre>";
	$smugglingHtml .= "</div>";
}

$smugglingHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
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
</form>";

?>
