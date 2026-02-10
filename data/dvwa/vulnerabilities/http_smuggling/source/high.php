<?php

$smugglingHtml = "";

// High: Rejects conflicting headers
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
	$smugglingHtml .= "<h3>Request Analysis (High Security)</h3>";
	
	// Reject conflicting headers
	if($has_cl && $has_te) {
		$smugglingHtml .= "<div style=\"background: #ffe6e6; padding: 15px; border: 2px solid #dc3545; border-radius: 5px; margin: 10px 0;\">";
		$smugglingHtml .= "<h4 style=\"color: #dc3545;\">❌ REQUEST REJECTED!</h4>";
		$smugglingHtml .= "<p><strong>Reason:</strong> Conflicting Content-Length and Transfer-Encoding headers detected.</p>";
		$smugglingHtml .= "<ul>";
		foreach($headers_info as $info) {
			$smugglingHtml .= "<li>" . htmlspecialchars($info) . "</li>";
		}
		$smugglingHtml .= "</ul>";
		$smugglingHtml .= "<p style=\"color: green;\"><strong>✓ Protection Active:</strong> Request smuggling attempt blocked.</p>";
		$smugglingHtml .= "<p>RFC 7230 states: \"If a message is received with both a Transfer-Encoding and a Content-Length header field, the Transfer-Encoding overrides the Content-Length.\"</p>";
		$smugglingHtml .= "<p>However, this ambiguity can be exploited. Best practice: <strong>reject</strong> such requests entirely.</p>";
		$smugglingHtml .= "</div>";
	} else if($has_cl) {
		$smugglingHtml .= "<div style=\"background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 5px;\">";
		$smugglingHtml .= "<p style=\"color: #28a745;\">✓ Request validated successfully</p>";
		$smugglingHtml .= "<p>Only Content-Length header found: $cl_value bytes</p>";
		$smugglingHtml .= "<p>No smuggling risk detected.</p>";
		$smugglingHtml .= "</div>";
	} else if($has_te) {
		$smugglingHtml .= "<div style=\"background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 5px;\">";
		$smugglingHtml .= "<p style=\"color: #28a745;\">✓ Request validated successfully</p>";
		$smugglingHtml .= "<p>Only Transfer-Encoding header found: $te_value</p>";
		$smugglingHtml .= "<p>No smuggling risk detected.</p>";
		$smugglingHtml .= "</div>";
	}
	
	$smugglingHtml .= "<h4>Your Request:</h4>";
	$smugglingHtml .= "<pre style=\"background: #f5f5f5; padding: 10px; border: 1px solid #ccc;\">" . htmlspecialchars($request_data) . "</pre>";
	$smugglingHtml .= "</div>";
}

$smugglingHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset>
		<legend>Test HTTP Request (High Security)</legend>
		<p>This level properly rejects requests with conflicting headers.</p>
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
		<p style=\"color: #666;\"><em>Try sending a request with both headers - it should be rejected.</em></p>
	</fieldset>
</form>";

?>
