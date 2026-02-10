<div class="body_padded">
	<h1>Help - Clickjacking (UI Redressing)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Clickjacking, also known as UI redressing attack, is a malicious technique where an attacker tricks a user into clicking on something different from what they perceive, potentially revealing confidential information or allowing attackers to take control of their account.</p>
					
					<p>The attacker loads the target page in a transparent iframe overlaid on a decoy page. When users think they're clicking on the decoy page (e.g., "Win a Free Prize!"), they're actually clicking on the hidden target page underneath (e.g., "Grant Admin Access").</p>

					<h3>How Clickjacking Works</h3>
					<ol>
						<li><strong>Attacker creates a malicious page</strong> with attractive content (free prizes, like buttons, games, etc.)</li>
						<li><strong>Target page is loaded in an invisible iframe</strong> using opacity: 0 or visibility: hidden</li>
						<li><strong>Buttons/links are positioned</strong> so that the target page's sensitive actions align with attractive decoy buttons</li>
						<li><strong>User clicks on the decoy</strong>, thinking they're interacting with the fake content</li>
						<li><strong>The click goes through to the hidden page</strong>, performing unintended sensitive actions</li>
					</ol>

					<h3>Attack Types</h3>
					<ul>
						<li><strong>Basic Clickjacking:</strong> Hide entire page and overlay fake UI</li>
						<li><strong>Likejacking:</strong> Trick users into liking social media pages</li>
						<li><strong>Cursorjacking:</strong> Shift cursor position to trick users about what they're clicking</li>
						<li><strong>Strokejacking/Keypress Injection:</strong> Capture keystrokes from hidden forms</li>
						<li><strong>Double Clickjacking:</strong> Require two clicks, defeating some JS protections</li>
					</ul>

					<h3>Example Attack Code</h3>
					<pre>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;style&gt;
        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;  /* Invisible iframe */
            z-index: 2;  /* Above other content */
        }
        .decoy {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 200px;
        }
        button {
            padding: 20px 40px;
            font-size: 24px;
            background: red;
            color: white;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class="decoy"&gt;
        &lt;h1&gt;Win $1000!&lt;/h1&gt;
        &lt;button&gt;CLICK TO WIN&lt;/button&gt;
    &lt;/div&gt;
    &lt;iframe src="http://target-site.com/delete-account"&gt;&lt;/iframe&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>

					<h3>Real-World Attack Scenarios</h3>
					<ul>
						<li><strong>Social Media:</strong> Like/Follow pages without user consent</li>
						<li><strong>Banking:</strong> Transfer funds, change account details</li>
						<li><strong>E-commerce:</strong> Make purchases, change shipping address</li>
						<li><strong>Admin Panels:</strong> Grant admin access, delete accounts</li>
						<li><strong>Webcam/Microphone:</strong> Grant permission to access hardware</li>
						<li><strong>OAuth:</strong> Authorize malicious applications</li>
					</ul>

					<h3>Impact</h3>
					<ul>
						<li>Unauthorized actions performed on behalf of victim</li>
						<li>Account takeover via permission changes</li>
						<li>Financial fraud via unauthorized transactions</li>
						<li>Privacy violation via webcam/microphone access</li>
						<li>Data theft via form submissions</li>
						<li>Malware installation via download confirmations</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to understand clickjacking vulnerabilities:</p>
					<ul>
						<li><strong>Low:</strong> Embed page in iframe with no protection</li>
						<li><strong>Medium:</strong> Test X-Frame-Options: SAMEORIGIN</li>
						<li><strong>High:</strong> Test X-Frame-Options: DENY</li>
						<li><strong>Impossible:</strong> Understand comprehensive clickjacking prevention</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>No clickjacking protection. The page can be embedded in any iframe from any origin.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Create an HTML file with an invisible iframe loading the DVWA clickjacking page, overlay it with attractive fake content, and trick users into clicking.</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>Uses X-Frame-Options: SAMEORIGIN. The page can only be framed by pages from the same origin.</p>
					<p><em>Spoiler:</em> <span class="spoiler">External sites cannot frame this page, but pages on dvwa.lab can still embed it. This protects against external attackers but not against XSS or same-origin attacks.</span></p>

					<br />

					<h3>High Level</h3>
					<p>Uses X-Frame-Options: DENY. The page cannot be embedded in any iframe, even from same origin.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The page refuses to load in any iframe. However, X-Frame-Options is deprecated and not supported by all browsers. Legacy browsers or custom browsers might bypass this.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>Comprehensive clickjacking protection with multiple layers:</p>
					<ul>
						<li><strong>X-Frame-Options: DENY</strong> - For older browser support</li>
						<li><strong>CSP frame-ancestors 'none'</strong> - Modern standard (supersedes X-Frame-Options)</li>
						<li><strong>CSRF Token Validation</strong> - Prevents unauthorized form submissions</li>
						<li><strong>JavaScript Frame Busting</strong> - Client-side detection and prevention</li>
					</ul>
					<p>This defense-in-depth approach ensures protection across all browsers and scenarios.</p>

					<br />

					<h3>Defense Strategies</h3>
					
					<h4>1. HTTP Headers (Recommended)</h4>
					<pre>X-Frame-Options: DENY
Content-Security-Policy: frame-ancestors 'none'</pre>
					
					<p>Or allow specific origins:</p>
					<pre>X-Frame-Options: ALLOW-FROM https://trusted-site.com
Content-Security-Policy: frame-ancestors 'self' https://trusted-site.com</pre>

					<h4>2. JavaScript Frame Busting</h4>
					<pre>&lt;script&gt;
if (top !== self) {
    top.location = self.location;
}
&lt;/script&gt;</pre>

					<p><strong>Note:</strong> JavaScript can be bypassed with the sandbox attribute:</p>
					<pre>&lt;iframe sandbox="allow-forms allow-scripts" src="..."&gt;&lt;/iframe&gt;</pre>

					<h4>3. SameSite Cookies</h4>
					<pre>Set-Cookie: session=abc123; SameSite=Strict</pre>
					<p>Prevents cookies from being sent in cross-site contexts.</p>

					<h4>4. User Confirmation</h4>
					<p>Require re-authentication or CAPTCHA for sensitive actions.</p>

					<h3>Header Comparison</h3>
					<table border="1" cellpadding="5" style="border-collapse: collapse;">
						<tr>
							<th>Header</th>
							<th>Value</th>
							<th>Protection</th>
							<th>Support</th>
						</tr>
						<tr>
							<td>X-Frame-Options</td>
							<td>DENY</td>
							<td>No framing</td>
							<td>Legacy browsers</td>
						</tr>
						<tr>
							<td>X-Frame-Options</td>
							<td>SAMEORIGIN</td>
							<td>Same-origin only</td>
							<td>Legacy browsers</td>
						</tr>
						<tr>
							<td>CSP</td>
							<td>frame-ancestors 'none'</td>
							<td>No framing</td>
							<td>Modern (recommended)</td>
						</tr>
						<tr>
							<td>CSP</td>
							<td>frame-ancestors 'self'</td>
							<td>Same-origin only</td>
							<td>Modern (recommended)</td>
						</tr>
					</table>

					<br />

					<h3>Testing Tips</h3>
					<ul>
						<li>Create test HTML file with iframe pointing to target</li>
						<li>Use browser developer tools to check response headers</li>
						<li>Test with opacity: 0.5 to see overlay alignment</li>
						<li>Try different z-index values for proper stacking</li>
						<li>Test iframe sandbox attribute to bypass JS frame-busting</li>
						<li>Use OWASP ZAP or Burp Suite for automated testing</li>
					</ul>

					<h3>Famous Incidents</h3>
					<ul>
						<li><strong>Twitter (2009):</strong> Clickjacking used to force users to follow accounts</li>
						<li><strong>Facebook (2010):</strong> Likejacking campaigns spread spam</li>
						<li><strong>Adobe Flash (2008):</strong> Clickjacking to enable webcam/microphone</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/Clickjacking', 'OWASP - Clickjacking' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/clickjacking', 'PortSwigger - Clickjacking' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Clickjacking_Defense_Cheat_Sheet.html', 'OWASP Clickjacking Defense' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options', 'MDN - X-Frame-Options' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors', 'MDN - CSP frame-ancestors' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
