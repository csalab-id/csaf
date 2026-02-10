<div class="body_padded">
	<h1>Help - Insecure Deserialization</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Insecure Deserialization is a vulnerability that occurs when an application deserializes (converts serialized data back into objects) untrusted data without proper validation. This can lead to remote code execution, privilege escalation, authentication bypass, and other attacks.</p>
					
					<p>Serialization is the process of converting complex data structures (like objects) into a format that can be easily stored or transmitted. Deserialization is the reverse process. When an application deserializes user-controlled data, attackers can craft malicious serialized objects that execute arbitrary code when deserialized.</p>

					<h3>How It Works</h3>
					<p>In PHP, the <code>serialize()</code> and <code>unserialize()</code> functions are used for serialization:</p>
					<pre>$data = serialize($object);  // Convert object to string
$object = unserialize($data); // Convert string back to object</pre>

					<p>The danger lies in PHP's "magic methods" that are automatically called during object lifecycle:</p>
					<ul>
						<li><code>__wakeup()</code> - Called when unserialize() is used</li>
						<li><code>__destruct()</code> - Called when object is destroyed</li>
						<li><code>__toString()</code> - Called when object is treated as string</li>
						<li><code>__call()</code> - Called when invoking inaccessible methods</li>
					</ul>

					<h3>Attack Techniques</h3>
					<p><strong>1. Direct Code Execution:</strong></p>
					<p>Craft objects with magic methods that execute system commands.</p>

					<p><strong>2. Property-Oriented Programming (POP):</strong></p>
					<p>Chain together existing classes in the application to achieve code execution (similar to ROP in binary exploitation).</p>

					<p><strong>3. Arbitrary File Operations:</strong></p>
					<p>Manipulate object properties to read/write files via __destruct() or other magic methods.</p>

					<h3>Example Exploit</h3>
					<p>For the vulnerable UserPreferences class:</p>
					<pre>&lt;?php
class UserPreferences {
    public $theme = "light";
    public $language = "en";
    public $file = "/etc/passwd";  // Attacker-controlled
}

$payload = serialize(new UserPreferences());
echo $payload;
// Result: O:15:"UserPreferences":3:{s:5:"theme";s:5:"light";s:8:"language";s:2:"en";s:4:"file";s:11:"/etc/passwd";}
?&gt;</pre>

					<p>When this payload is deserialized, the __destruct() method reads /etc/passwd!</p>

					<h3>Real-World Payloads</h3>
					<p><strong>Read /etc/passwd:</strong></p>
					<pre>O:15:"UserPreferences":3:{s:5:"theme";s:5:"light";s:8:"language";s:2:"en";s:4:"file";s:11:"/etc/passwd";}</pre>

					<p><strong>Read config files:</strong></p>
					<pre>O:15:"UserPreferences":3:{s:5:"theme";s:4:"dark";s:8:"language";s:2:"fr";s:4:"file";s:30:"../../../config/config.inc.php";}</pre>

					<h3>Impact</h3>
					<ul>
						<li><strong>Remote Code Execution:</strong> Execute arbitrary commands on the server</li>
						<li><strong>Arbitrary File Read/Write:</strong> Access sensitive files or modify application files</li>
						<li><strong>Authentication Bypass:</strong> Manipulate session objects or user objects</li>
						<li><strong>SQL Injection:</strong> Inject queries via object properties</li>
						<li><strong>Denial of Service:</strong> Crash application with malformed objects</li>
						<li><strong>Privilege Escalation:</strong> Modify user role/permission objects</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit the insecure deserialization vulnerability to:</p>
					<ul>
						<li><strong>Low:</strong> Read sensitive files using magic methods</li>
						<li><strong>Medium:</strong> Bypass basic class name validation</li>
						<li><strong>High:</strong> Bypass property filtering and validation</li>
						<li><strong>Impossible:</strong> Understand proper deserialization prevention</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>Direct unserialize() with no validation. The UserPreferences class has a __destruct() method that reads files.</p>
					<p><em>Spoiler:</em> <span class="spoiler">Create a UserPreferences object with the $file property set to /etc/passwd or other sensitive files, serialize it, and submit it.</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>Basic validation checking for "UserPreferences" string in the serialized data.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The validation only checks if "UserPreferences" is present, but you can still create malicious UserPreferences objects with the $file property set. The validation is easily bypassed.</span></p>

					<br />

					<h3>High Level</h3>
					<p>Blocks serialized data containing "file" property name, and validates after deserialization.</p>
					<p><em>Spoiler:</em> <span class="spoiler">The string "file" is checked before deserialization, making it harder. However, in real scenarios, you could use other magic methods, POP chains with different properties, or encoding techniques. This level demonstrates stronger but still imperfect validation.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>The impossible level properly prevents deserialization attacks by:</p>
					<ul>
						<li>Using CSRF token protection</li>
						<li><strong>Using JSON instead of PHP serialize/unserialize</strong></li>
						<li>JSON only represents data, not objects or code</li>
						<li>Validating data structure after JSON decode</li>
						<li>Using whitelist validation for all values</li>
						<li>Never instantiating objects from user input</li>
					</ul>
					<p>This is the recommended approach for safe data serialization.</p>

					<br />

					<h3>Defense Strategies</h3>
					<ul>
						<li><strong>Avoid unserialize():</strong> Never use unserialize() on user-supplied data</li>
						<li><strong>Use JSON:</strong> Use json_encode()/json_decode() instead - JSON cannot execute code</li>
						<li><strong>Use safe formats:</strong> XML, YAML (with safe loader), Protocol Buffers</li>
						<li><strong>Integrity checks:</strong> Use HMAC to verify serialized data hasn't been tampered with</li>
						<li><strong>Whitelist classes:</strong> If unserialize() is necessary, use <code>allowed_classes</code> option (PHP 7+)</li>
						<li><strong>Input validation:</strong> Strictly validate structure and values after deserialization</li>
						<li><strong>Disable magic methods:</strong> Avoid __wakeup(), __destruct() in sensitive classes</li>
						<li><strong>Principle of least privilege:</strong> Run with minimal permissions</li>
					</ul>

					<h3>PHP Safe Unserialize (PHP 7+)</h3>
					<pre>// Only allow specific classes
$data = unserialize($input, ["allowed_classes" => ["UserPreferences"]]);

// Or block all classes
$data = unserialize($input, ["allowed_classes" => false]);</pre>

					<h3>Testing Tips</h3>
					<ul>
						<li>Save legitimate preferences first to see the serialized format</li>
						<li>Modify the serialized string to change property values</li>
						<li>Try reading: /etc/passwd, /etc/hostname, ../../../config/config.inc.php</li>
						<li>Use tools like phpggc for generating POP chain payloads</li>
						<li>Look for classes with dangerous magic methods in the codebase</li>
					</ul>

					<h3>Famous Vulnerabilities</h3>
					<ul>
						<li><strong>Joomla (2015):</strong> RCE via PHP object injection</li>
						<li><strong>Drupal (2019):</strong> SQL injection via deserialization</li>
						<li><strong>Apache Commons (2015):</strong> Java deserialization RCE</li>
						<li><strong>Ruby on Rails (2013):</strong> YAML deserialization RCE</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/vulnerabilities/Deserialization_of_untrusted_data', 'OWASP - Deserialization' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/deserialization', 'PortSwigger - Insecure Deserialization' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Deserialization_Cheat_Sheet.html', 'OWASP Deserialization Prevention' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-top-ten/2017/A8_2017-Insecure_Deserialization', 'OWASP Top 10 2017 - A8' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://github.com/ambionics/phpggc', 'PHPGGC - PHP unserialize() payloads' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
