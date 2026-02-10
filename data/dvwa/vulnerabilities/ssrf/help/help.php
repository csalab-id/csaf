<div class="body_padded">
	<h1>Help - Server-Side Request Forgery (SSRF)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Server-Side Request Forgery (SSRF) is a web security vulnerability that allows an attacker to induce the server-side application to make HTTP requests to an arbitrary domain of the attacker's choosing.</p>
					
					<p>In a typical SSRF attack, the attacker might cause the server to make a connection to internal-only services within the organization's infrastructure. In other cases, they may be able to force the server to connect to arbitrary external systems, potentially leaking sensitive data.</p>

					<h3>Common SSRF Targets</h3>
					<ul>
						<li>Internal services and APIs (e.g., http://localhost:8080/admin)</li>
						<li>Cloud metadata services (e.g., http://169.254.169.254/latest/meta-data/)</li>
						<li>Internal networks (e.g., http://192.168.1.1/)</li>
						<li>File protocol handlers (e.g., file:///etc/passwd)</li>
						<li>Non-HTTP protocols (e.g., dict://, gopher://, ftp://)</li>
					</ul>

					<h3>Impact</h3>
					<ul>
						<li>Access to internal services and APIs</li>
						<li>Reading sensitive files via file:// protocol</li>
						<li>Port scanning internal networks</li>
						<li>Bypassing firewall rules and access controls</li>
						<li>Accessing cloud metadata services to steal credentials</li>
						<li>Denial of Service (DoS) attacks</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the SSRF vulnerability to:</p>
					<ul>
						<li><strong>Low:</strong> Access localhost or internal services</li>
						<li><strong>Medium:</strong> Bypass basic URL filtering</li>
						<li><strong>High:</strong> Bypass advanced filtering mechanisms</li>
						<li><strong>Impossible:</strong> Understand proper SSRF prevention techniques</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level has no protection whatsoever. Any URL can be fetched by the server.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Try accessing http://localhost/, http://127.0.0.1/, or file:///etc/passwd</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level implements a basic blacklist to block obvious localhost references.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Try variations like http://127.1/, http://0/, http://[::1]/, or http://localtest.me/. You can also use DNS rebinding or redirects.</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level implements more comprehensive blocking including private IP ranges and protocol filtering.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Try URL encoding (http://127.0.0.1 = http://2130706433/), decimal/octal/hex IP representations, or DNS-based bypasses with domains that resolve to localhost. Open redirects can also help bypass these filters.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level uses a whitelist approach where only specific trusted domains are allowed. It also:</p>
					<ul>
						<li>Uses POST with CSRF token</li>
						<li>Validates URL structure with parse_url()</li>
						<li>Only allows http:// and https:// schemes</li>
						<li>Implements domain whitelist</li>
						<li>Resolves DNS and checks if IP is private/reserved</li>
						<li>Disables redirects</li>
						<li>Limits response size</li>
						<li>Sets timeout to prevent DoS</li>
					</ul>
					<p>This is the recommended approach for preventing SSRF vulnerabilities.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Whitelist approach:</strong> Only allow requests to specific trusted domains/IPs</li>
						<li><strong>Input validation:</strong> Validate and sanitize all user-supplied URLs</li>
						<li><strong>DNS resolution checks:</strong> Resolve domain and verify it's not a private/reserved IP</li>
						<li><strong>Disable redirects:</strong> Prevent redirect-based bypasses</li>
						<li><strong>Protocol restriction:</strong> Only allow http/https, block file://, gopher://, etc.</li>
						<li><strong>Network segmentation:</strong> Isolate application servers from internal networks</li>
						<li><strong>Use allow lists for IPs/domains</strong></li>
						<li><strong>Implement proper authentication</strong> for internal services</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/Top10/A10_2021-Server-Side_Request_Forgery_%28SSRF%29/', 'OWASP Top 10 - SSRF' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/ssrf', 'PortSwigger - SSRF' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Server_Side_Request_Forgery_Prevention_Cheat_Sheet.html', 'OWASP SSRF Prevention Cheat Sheet' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://book.hacktricks.xyz/pentesting-web/ssrf-server-side-request-forgery', 'HackTricks - SSRF' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
