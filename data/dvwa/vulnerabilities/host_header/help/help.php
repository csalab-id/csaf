<?php

if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
	define( 'DVWA_WEB_PAGE_TO_ROOT', '../../../../' );
}

require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';
?>

<div class="body_padded">
	<h1>Help - Host Header Injection</h1>

	<div id="code">
		<table width="100%" bgcolor="white" style="border:2px #C0C0C0 solid">
			<tr>
				<td>
					<div id="code">
						<h3>About</h3>
						<p>Host Header Injection is a vulnerability that occurs when a web application implicitly trusts the HTTP Host header and uses it to generate content, links, or make security decisions without proper validation. Since the Host header is user-controllable, attackers can manipulate it to conduct various attacks including password reset poisoning, cache poisoning, and SSRF.</p>

						<p>The Host header is meant to indicate which website on a multi-tenant server the client wishes to access. However, many applications use it to:</p>
						<ul>
							<li>Generate absolute URLs in emails (password resets, confirmations)</li>
							<li>Create redirect locations</li>
							<li>Determine API endpoints</li>
							<li>Build canonical URLs</li>
							<li>Make internal API calls</li>
						</ul>

						<h3>Why It's Dangerous</h3>
						<p>Modern web applications often sit behind reverse proxies, load balancers, and CDNs. These intermediate layers may pass various host-related headers:</p>
						<ul>
							<li><code>Host:</code> - Standard HTTP/1.1 header</li>
							<li><code>X-Forwarded-Host:</code> - Original host from proxy</li>
							<li><code>X-Host:</code> - Alternative host header</li>
							<li><code>X-Forwarded-Server:</code> - Server name from proxy</li>
							<li><code>Forwarded:</code> - RFC 7239 standard header</li>
						</ul>
						<p>If the application trusts any of these headers without validation, attackers can inject malicious hosts.</p>

						<h3>Attack Scenarios</h3>

						<h4>1. Password Reset Poisoning</h4>
						<p>The most common and critical exploit:</p>
						<pre>POST /forgot-password HTTP/1.1
Host: attacker.com
Content-Type: application/x-www-form-urlencoded

email=victim@example.com</pre>
						
						<p>If the application uses the Host header to build the reset link:</p>
						<pre>// Vulnerable code
$reset_url = 'https://' . $_SERVER['HTTP_HOST'] . '/reset?token=' . $token;
mail($email, 'Password Reset', 'Click: ' . $reset_url);</pre>
						
						<p>Victim receives: <code>https://attacker.com/reset?token=secret123</code></p>
						<p>When victim clicks, attacker captures the token and resets victim's password.</p>

						<h4>2. Web Cache Poisoning</h4>
						<p>Inject malicious host to poison cached content:</p>
						<pre>GET /static/app.js HTTP/1.1
Host: attacker.com
X-Forwarded-Host: trusted-site.com</pre>
						
						<p>If response includes:</p>
						<pre>&lt;script src="https://attacker.com/static/malicious.js"&gt;&lt;/script&gt;</pre>
						
						<p>And the response is cached, all users receive the malicious script.</p>

						<h4>3. SSRF via Host Header</h4>
						<p>Force application to make requests to internal resources:</p>
						<pre>GET /api/fetch-data HTTP/1.1
Host: 192.168.1.1:8080</pre>
						
						<p>If application uses Host for API calls:</p>
						<pre>// Vulnerable
$api_url = 'http://' . $_SERVER['HTTP_HOST'] . '/internal/api';
$response = file_get_contents($api_url);</pre>
						
						<p>Attacker can access internal services (databases, admin panels, cloud metadata).</p>

						<h4>4. Authentication Bypass</h4>
						<p>Some applications validate hosts for authentication:</p>
						<pre>// Vulnerable authentication check
if ($_SERVER['HTTP_HOST'] === 'admin.company.com') {
    // Grant admin access
}</pre>
						
						<p>Attacker simply sets: <code>Host: admin.company.com</code></p>

						<h4>5. Virtual Host Confusion</h4>
						<p>On shared hosting, different domains may share same IP. Attacking one site can affect others:</p>
						<pre>GET /upload.php HTTP/1.1
Host: victim-site.com

[upload malicious file]

GET /uploads/malicious.php HTTP/1.1
Host: your-site.com</pre>

						<br /><hr /><br />

						<h3>Objective</h3>

						<p><span class="vuln_label">Low Level:</span> Directly uses <code>$_SERVER['HTTP_HOST']</code> without any validation. Demonstrates how password reset links can be poisoned by manipulating the Host header.</p>

						<p><span class="vuln_label">Medium Level:</span> Implements basic validation checking if certain keywords appear in the host. However, this is bypassable using techniques like:
						<ul>
							<li><code>dvwa.attacker.com</code> - subdomain</li>
							<li><code>attacker.com#dvwa</code> - fragment</li>
							<li><code>attacker.com?dvwa=1</code> - query parameter</li>
							<li>Alternative headers like <code>X-Forwarded-Host</code></li>
						</ul>
						</p>

						<p><span class="vuln_label">High Level:</span> Uses a whitelist of allowed hosts with strict validation. Properly removes ports and performs case-insensitive exact matching. Ignores alternative host headers.</p>

						<p><span class="vuln_label">Impossible Level:</span> Maximum security implementation:
						<ul>
							<li>Hardcoded trusted domain constant</li>
							<li>Host header completely ignored for URL generation</li>
							<li>CSRF token validation</li>
							<li>Rejection of alternative host headers</li>
							<li>SERVER_NAME validation</li>
							<li>Rate limiting</li>
							<li>Cryptographically secure tokens (256-bit)</li>
							<li>Token metadata binding (IP, User-Agent)</li>
							<li>Comprehensive audit logging</li>
						</ul>
						</p>

						<br /><hr /><br />

						<h3>Defense Mechanisms</h3>

						<h4>1. Use Hardcoded Domains (BEST)</h4>
						<pre>// ✅ SECURE: Never trust Host header for security-critical operations
define('TRUSTED_DOMAIN', 'example.com');
define('TRUSTED_PROTOCOL', 'https');

$reset_url = TRUSTED_PROTOCOL . '://' . TRUSTED_DOMAIN . '/reset?token=' . $token;</pre>

						<h4>2. Whitelist Validation</h4>
						<pre>// Whitelist of allowed hosts
$allowed_hosts = ['example.com', 'www.example.com', 'api.example.com'];

$host = $_SERVER['HTTP_HOST'];
$host = preg_replace('/:\d+$/', '', $host); // Remove port

if (!in_array(strtolower($host), $allowed_hosts, true)) {
    http_response_code(400);
    die('Invalid host header');
}</pre>

						<h4>3. Use SERVER_NAME Instead of HTTP_HOST</h4>
						<p><code>$_SERVER['SERVER_NAME']</code> comes from server configuration (not user input):</p>
						<pre>// Prefer SERVER_NAME over HTTP_HOST
$host = $_SERVER['SERVER_NAME'];  // From server config
// NOT: $_SERVER['HTTP_HOST'];    // From user input</pre>

						<h4>4. Reject Alternative Headers</h4>
						<pre>// Don't trust proxy headers without validation
$dangerous_headers = [
    'HTTP_X_FORWARDED_HOST',
    'HTTP_X_HOST',
    'HTTP_X_FORWARDED_SERVER',
    'HTTP_FORWARDED'
];

foreach ($dangerous_headers as $header) {
    if (isset($_SERVER[$header])) {
        // Log and reject
        error_log("Suspicious header: $header");
        http_response_code(400);
        die('Invalid request headers');
    }
}</pre>

						<h4>5. Web Server Configuration</h4>
						
						<p><strong>Apache:</strong></p>
						<pre># Reject requests with invalid Host headers
&lt;VirtualHost *:80&gt;
    ServerName example.com
    ServerAlias www.example.com
    
    # Reject invalid hosts
    UseCanonicalName On
    
    # Don't pass untrusted headers to application
    RequestHeader unset X-Forwarded-Host
    RequestHeader unset X-Host
&lt;/VirtualHost&gt;

# Catch-all vhost for invalid hosts
&lt;VirtualHost *:80&gt;
    ServerName _
    &lt;Location /&gt;
        Require all denied
    &lt;/Location&gt;
&lt;/VirtualHost&gt;</pre>

						<p><strong>Nginx:</strong></p>
						<pre># Default server to catch invalid hosts
server {
    listen 80 default_server;
    server_name _;
    return 444;  # Close connection
}

# Your actual server
server {
    listen 80;
    server_name example.com www.example.com;
    
    # Don't pass untrusted headers
    proxy_set_header Host $host;
    # NOT: proxy_set_header Host $http_host;
}</pre>

						<h4>6. Content Security Policy</h4>
						<pre>// Prevent injection into script sources
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://trusted-cdn.com");</pre>

						<br /><hr /><br />

						<h3>Testing for Host Header Injection</h3>

						<h4>1. Basic Test - Modify Host Header</h4>
						<pre>curl -H "Host: attacker.com" https://target.com/</pre>
						<p>Check response for:</p>
						<ul>
							<li>Absolute URLs containing attacker.com</li>
							<li>Redirects to attacker.com</li>
							<li>JavaScript references to attacker.com</li>
						</ul>

						<h4>2. Password Reset Poisoning Test</h4>
						<pre>curl -X POST https://target.com/forgot-password \\
  -H "Host: attacker.com" \\
  -d "email=your-test-account@example.com"</pre>
						<p>Check email for reset link pointing to attacker.com</p>

						<h4>3. Alternative Headers Test</h4>
						<pre>curl https://target.com/ \\
  -H "X-Forwarded-Host: attacker.com" \\
  -H "X-Host: attacker.com" \\
  -H "X-Forwarded-Server: attacker.com" \\
  -H "Forwarded: host=attacker.com"</pre>

						<h4>4. Subdomain Bypass Test</h4>
						<pre># If validation checks for "trusted.com"
curl -H "Host: trusted.com.attacker.com" https://target.com/
curl -H "Host: attacker.com#trusted.com" https://target.com/
curl -H "Host: attacker.com?trusted.com" https://target.com/</pre>

						<h4>5. Port Manipulation</h4>
						<pre>curl -H "Host: attacker.com:80" https://target.com/
curl -H "Host: attacker.com:@trusted.com" https://target.com/</pre>

						<h4>6. SSRF Test</h4>
						<pre>curl -H "Host: 127.0.0.1:8080" https://target.com/
curl -H "Host: 169.254.169.254" https://target.com/  # AWS metadata
curl -H "Host: localhost:6379" https://target.com/    # Redis</pre>

						<br /><hr /><br />

						<h3>Real-World Examples</h3>
						<ul>
							<li><strong>PayPal (2015):</strong> Password reset poisoning led to account takeover</li>
							<li><strong>Django (CVE-2012-4520):</strong> Host header validation bypass</li>
							<li><strong>Ruby on Rails:</strong> Multiple issues with trusted proxy headers</li>
							<li><strong>Magento:</strong> Cache poisoning via X-Forwarded-Host</li>
							<li><strong>Jira:</strong> Host header injection in email templates</li>
						</ul>

						<br /><hr /><br />

						<h3>Tools</h3>
						<ul>
							<li><strong>Burp Suite:</strong> Manual header manipulation in Repeater</li>
							<li><strong>wfuzz:</strong> Automated fuzzing of headers</li>
							<li><strong>httpheaderinjection:</strong> Specialized testing tool</li>
							<li><strong>curl:</strong> Simple header manipulation</li>
						</ul>

						<br /><hr /><br />

						<h3>References</h3>
						<?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/host-header', 'PortSwigger - Host Header Attacks' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/17-Testing_for_Host_Header_Injection', 'OWASP - Host Header Injection Testing' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://cwe.mitre.org/data/definitions/644.html', 'CWE-644: Improper Neutralization of HTTP Headers' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://www.acunetix.com/blog/articles/automated-detection-of-host-header-attacks/', 'Acunetix - Host Header Attack Detection' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html', 'Practical HTTP Host Header Attacks' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://tools.ietf.org/html/rfc7230#section-5.4', 'RFC 7230 - HTTP/1.1 Host Header' ); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>
