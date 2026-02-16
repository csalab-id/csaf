<div class="body_padded">
	<h1>Help - Insecure Direct Object Reference (IDOR)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Insecure Direct Object References (IDOR) occur when an application provides direct access to objects based on user-supplied input. As a result, attackers can bypass authorization and access resources directly by modifying the value of a parameter used to point to an object.</p>
					
					<p>IDOR vulnerabilities are among the most common access control flaws. They occur when:</p>
					<ul>
						<li>The application uses user-controlled input to access objects directly (e.g., user IDs, file names, database keys)</li>
						<li>The application doesn't verify that the user is authorized to access the requested object</li>
						<li>Object references are predictable (sequential IDs, simple encoding)</li>
					</ul>

					<h3>IDOR Attack Scenarios</h3>
					<ul>
						<li><strong>User Profile Access:</strong> Changing user_id parameter to view other users' profiles</li>
						<li><strong>Document Access:</strong> Modifying document IDs to access confidential files</li>
						<li><strong>Account Takeover:</strong> Manipulating user references during login or session</li>
						<li><strong>Financial Data:</strong> Accessing other users' invoices, transactions, or payment info</li>
						<li><strong>API Exploitation:</strong> Manipulating object IDs in API requests</li>
					</ul>

					<h3>Common IDOR Examples</h3>
					<p><strong>URL Parameter:</strong></p>
					<pre>https://example.com/profile?user_id=123
https://example.com/document?doc_id=456</pre>

					<p><strong>Hidden Form Field:</strong></p>
					<pre>&lt;input type="hidden" name="user_id" value="123"&gt;</pre>

					<p><strong>Cookie Value:</strong></p>
					<pre>Cookie: user_id=123</pre>

					<p><strong>API Request:</strong></p>
					<pre>GET /api/users/123/profile
DELETE /api/orders/456</pre>

					<h3>Impact</h3>
					<ul>
						<li>Unauthorized access to sensitive data (PII, financial records, medical info)</li>
						<li>Privilege escalation (accessing admin functions)</li>
						<li>Account takeover by accessing other users' sessions</li>
						<li>Data manipulation or deletion of other users' resources</li>
						<li>Privacy violations and regulatory compliance issues</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the IDOR vulnerability to login as admin:</p>
					<ul>
						<li><strong>Low:</strong> Manipulate the exposed user_id parameter</li>
						<li><strong>Medium:</strong> Bypass numeric-only user_id validation</li>
						<li><strong>High:</strong> Decode and manipulate the base64-encoded user token</li>
						<li><strong>Impossible:</strong> Understand proper authorization and session management</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level exposes the user_id directly in the form as a text field that can be easily modified. The application:</p>
					<ul>
						<li>Uses GET method (parameters visible in URL)</li>
						<li>Accepts user_id from user input without validation</li>
						<li>Does not verify if the authenticated user matches the user_id</li>
						<li>Directly queries database using the supplied user_id</li>
					</ul>
					<p><em>Spoiler:</em> <span class="spoiler">Simply change the user_id value from 2 to 1 in the form field. User ID 1 is typically the admin account. You can login with any valid credentials (like gordonb/abc123) but change user_id to 1 to become admin.</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level adds basic validation:</p>
					<ul>
						<li>Uses POST method instead of GET</li>
						<li>Validates that user_id is numeric using is_numeric()</li>
						<li>Still accepts user-supplied user_id without authorization check</li>
						<li>Does not verify if authenticated user matches the requested user_id</li>
					</ul>
					<p><em>Spoiler:</em> <span class="spoiler">The numeric validation doesn't prevent IDOR. You still provide numeric user_id values. Change user_id from 2 to 1 to access admin account. The POST method makes it slightly less obvious than GET, but you can still modify the form field value before submission or use browser DevTools to edit it.</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level attempts to obscure the user_id:</p>
					<ul>
						<li>Uses POST method with CSRF token</li>
						<li>Encodes user_id using base64 encoding</li>
						<li>Requires decoding before processing</li>
						<li>Still no verification that authenticated user matches decoded user_id</li>
					</ul>
					<p>While base64 encoding provides obscurity, it's not security. The encoding is easily reversible.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Base64 encode the admin user_id. If the default token is "Mg==" (base64 for "2"), change it to "MQ==" (base64 for "1"). You can use command line: `echo -n "1" | base64` or online tools. The CSRF token prevents CSRF attacks but doesn't prevent IDOR exploitation when you control the user_id parameter.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level properly prevents IDOR by:</p>
					<ul>
						<li>Using POST method with CSRF token protection</li>
						<li>NOT accepting user_id from user input at all</li>
						<li>Fetching user data ONLY based on authenticated credentials</li>
						<li>Using session management to track logged-in user</li>
						<li>Implementing proper authorization checks</li>
						<li>Adding rate limiting with sleep() for failed logins</li>
					</ul>
					<p>This implementation ensures users can only access their own data. To login as admin, you must know the actual admin credentials.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Never trust user input:</strong> Don't accept object identifiers directly from users</li>
						<li><strong>Use indirect references:</strong> Map user input to actual object IDs server-side</li>
						<li><strong>Implement authorization checks:</strong> Always verify user has permission to access the object</li>
						<li><strong>Use session-based references:</strong> Store object ownership in server session, not client</li>
						<li><strong>Validate ownership:</strong> Check if requested object belongs to authenticated user</li>
						<li><strong>Use UUIDs instead of sequential IDs:</strong> Makes guessing object IDs much harder</li>
						<li><strong>Implement access control lists (ACLs):</strong> Define who can access what resources</li>
						<li><strong>Log access attempts:</strong> Monitor and alert on suspicious access patterns</li>
					</ul>

					<br />

					<h3>Testing for IDOR</h3>
					<p>Steps to test for IDOR vulnerabilities:</p>
					<ol>
						<li>Identify all parameters that reference objects (IDs, keys, filenames)</li>
						<li>Create two accounts (or use different privilege levels)</li>
						<li>Access a resource with Account A and note the object reference</li>
						<li>Login as Account B and try to access Account A's resource by changing the reference</li>
						<li>Test with sequential IDs (try ID+1, ID-1, etc.)</li>
						<li>Try to access administrative functions with low-privilege account</li>
						<li>Check if encoding (base64, hex) is used and try to decode/modify</li>
						<li>Test API endpoints with different user IDs</li>
					</ol>

					<br />

					<h3>Real-World IDOR Examples</h3>
					<ul>
						<li><strong>Facebook:</strong> Video privacy bypass allowing access to private videos</li>
						<li><strong>Instagram:</strong> Accessing private photos by manipulating media IDs</li>
						<li><strong>Bank APIs:</strong> Reading other users' account balances and transactions</li>
						<li><strong>Healthcare portals:</strong> Accessing patient records by changing patient IDs</li>
						<li><strong>E-commerce:</strong> Viewing and modifying other users' orders</li>
					</ul>

					<br />

					<h3>Prevention Code Example (PHP)</h3>
					<pre>// VULNERABLE CODE (IDOR)
$user_id = $_GET['user_id'];
$query = "SELECT * FROM users WHERE user_id = $user_id";

// SECURE CODE (With Authorization)
$user_id = $_GET['user_id'];
$logged_in_user_id = $_SESSION['user_id'];

// Verify ownership
if ($user_id != $logged_in_user_id && !isAdmin($logged_in_user_id)) {
    die("Access denied");
}

$query = "SELECT * FROM users WHERE user_id = $user_id";
</pre>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/05-Authorization_Testing/04-Testing_for_Insecure_Direct_Object_References', 'OWASP - Testing for IDOR' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/access-control/idor', 'PortSwigger - Insecure Direct Object References' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Insecure_Direct_Object_Reference_Prevention_Cheat_Sheet.html', 'OWASP IDOR Prevention Cheat Sheet' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cwe.mitre.org/data/definitions/639.html', 'CWE-639: Authorization Bypass Through User-Controlled Key' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/access-control', 'PortSwigger - Access Control Vulnerabilities' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
