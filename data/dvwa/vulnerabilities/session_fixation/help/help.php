<div class="body_padded">
	<h1>Help - Session Fixation</h1>

	<div id="code">
		<table width="100%" bgcolor="white" style="border:2px #C0C0C0 solid">
			<tr>
				<td>
					<div id="code">
						<h3>About</h3>
						<p>Session fixation is an attack that allows an attacker to hijack a valid user session by "fixing" the session ID before the victim authenticates. The attacker first obtains or sets a session ID, then tricks the victim into using that same session ID (typically via a crafted URL or cookie). When the victim logs in, the application associates their authentication with the fixed session ID, which the attacker can then use to impersonate the victim.</p>

						<p>This vulnerability occurs when applications accept session IDs from URL parameters or cookies without proper validation, and fail to regenerate the session ID after authentication.</p>

						<h3>Attack Scenario</h3>
						<p><strong>Step 1:</strong> Attacker visits the target site and obtains a session ID (e.g., PHPSESSID=abc123)</p>
						<p><strong>Step 2:</strong> Attacker sends victim a malicious link containing the fixed session ID:<br>
						<code>http://vulnerable-site.com/login.php?PHPSESSID=abc123</code></p>
						<p><strong>Step 3:</strong> Victim clicks the link and logs in with valid credentials</p>
						<p><strong>Step 4:</strong> Since the session ID wasn't regenerated, the attacker can now use the same session ID (abc123) to access the victim's authenticated session</p>

						<h3>Common Attack Vectors</h3>
						<ul>
							<li><strong>URL Parameter:</strong> <code>?PHPSESSID=fixed_value</code></li>
							<li><strong>Cookie Injection:</strong> Setting cookie via JavaScript or HTTP headers</li>
							<li><strong>Hidden Form Fields:</strong> Pre-filled session IDs in forms</li>
							<li><strong>Meta Tags:</strong> Using meta refresh to set cookies</li>
						</ul>

						<h3>Real-World Impact</h3>
						<ul>
							<li>Complete account takeover without knowing the victim's password</li>
							<li>Access to sensitive personal or financial information</li>
							<li>Ability to perform actions as the victim (unauthorized transactions, data modification)</li>
							<li>Privilege escalation if victim has elevated permissions</li>
						</ul>

						<br /><hr /><br />

						<h3>Objective</h3>
						<p>Each security level demonstrates different protection mechanisms:</p>

						<p><span class="vuln_label">Low Level:</span> Accepts session IDs from URL parameters with no validation. Session ID is never regenerated.<br>
						<em>Test:</em> Visit the page with <code>?PHPSESSID=attacker_controlled_id</code> then login.</p>

						<p><span class="vuln_label">Medium Level:</span> Blocks URL-based session fixation but doesn't regenerate session IDs on login.<br>
						<em>Still vulnerable to cookie-based attacks and session adoption.</em></p>

						<p><span class="vuln_label">High Level:</span> Regenerates session ID after successful authentication using <code>session_regenerate_id(true)</code>.<br>
						<em>Proper defense against session fixation attacks.</em></p>

						<p><span class="vuln_label">Impossible Level:</span> Multiple security layers including:
						<ul>
							<li>Session ID regeneration on authentication</li>
							<li>CSRF token validation</li>
							<li>Session binding to IP address and User-Agent</li>
							<li>Secure cookie settings (HttpOnly, SameSite)</li>
							<li>Session timeout implementation</li>
							<li>Detection of suspicious session manipulation attempts</li>
						</ul>
						</p>

						<br /><hr /><br />

						<h3>Defense Mechanisms</h3>

						<h4>Essential Protections:</h4>
						<ol>
							<li><strong>Session Regeneration:</strong>
								<pre>// Regenerate session ID after login
session_regenerate_id(true); // true = delete old session</pre>
							</li>

							<li><strong>Reject External Session IDs:</strong>
								<pre>// Don't accept session IDs from GET/POST
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);</pre>
							</li>

							<li><strong>Secure Cookie Settings:</strong>
								<pre>session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'yourdomain.com',
    'secure' => true,  // HTTPS only
    'httponly' => true, // No JavaScript access
    'samesite' => 'Strict'
]);</pre>
							</li>
						</ol>

						<h4>Additional Hardening:</h4>
						<ul>
							<li><strong>IP Binding:</strong> Validate that session IP matches user IP</li>
							<li><strong>User-Agent Binding:</strong> Check User-Agent consistency</li>
							<li><strong>Session Timeout:</strong> Expire sessions after period of inactivity</li>
							<li><strong>CSRF Protection:</strong> Use anti-CSRF tokens for sensitive operations</li>
							<li><strong>Logout Cleanup:</strong> Properly destroy sessions on logout</li>
						</ul>

						<h4>Secure Session Management Example:</h4>
						<pre>// On login success
$old_session = session_id();
session_regenerate_id(true);

$_SESSION['user_id'] = $user_id;
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
$_SESSION['login_time'] = time();

// On each request
if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    die('Session hijacking detected');
}

if (time() - $_SESSION['login_time'] > 1800) {
    session_destroy();
    die('Session expired');
}</pre>

						<br /><hr /><br />

						<h3>Testing Methods</h3>

						<h4>Manual Testing:</h4>
						<ol>
							<li>Open the target login page in your browser</li>
							<li>Note the current session ID (check cookies or use browser dev tools)</li>
							<li>Try appending <code>?PHPSESSID=your_fixed_id</code> to the URL</li>
							<li>Log in with valid credentials</li>
							<li>Check if the session ID remains the same after login</li>
							<li>In a different browser/incognito window, use the same session ID to see if you're authenticated</li>
						</ol>

						<h4>Automated Testing with cURL:</h4>
						<pre># Step 1: Get initial session
curl -i http://target/login.php

# Step 2: Send to victim with fixed session ID
curl -i http://target/login.php \\
  -H "Cookie: PHPSESSID=fixed_session_id" \\
  -d "username=victim&password=password"

# Step 3: Use fixed session as attacker
curl -i http://target/account.php \\
  -H "Cookie: PHPSESSID=fixed_session_id"</pre>

						<br /><hr /><br />

						<h3>References</h3>
						<?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/Session_fixation', 'OWASP - Session Fixation' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html', 'OWASP Session Management Cheat Sheet' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/authentication/other-mechanisms', 'PortSwigger - Authentication Vulnerabilities' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://cwe.mitre.org/data/definitions/384.html', 'CWE-384: Session Fixation' ); ?><br>
						<?php echo dvwaExternalLinkUrlGet( 'https://www.php.net/manual/en/function.session-regenerate-id.php', 'PHP session_regenerate_id() Documentation' ); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>
