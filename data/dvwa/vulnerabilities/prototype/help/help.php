<div class="body_padded">
	<h1>Help - Prototype Pollution</h1>

	<div id="code">
		<table width='100%' bgcolor='white' style="border:2px #C0C0C0 solid">
			<tr>
				<td><div id="code">
					<h3>About</h3>
					<p>Prototype Pollution is a JavaScript vulnerability that occurs when an attacker can inject properties into Object.prototype or other built-in prototypes. Since JavaScript objects inherit properties from their prototype, polluting the prototype affects all objects in the application.</p>
					
					<p>In JavaScript, every object has a prototype chain. When you access a property that doesn't exist on an object, JavaScript looks up the prototype chain. If an attacker can add properties to Object.prototype, those properties will appear on ALL objects.</p>

					<h3>JavaScript Prototype Basics</h3>
					<pre>const obj = {};
obj.test;  // undefined

Object.prototype.test = 'polluted';

const newObj = {};
newObj.test;  // 'polluted' - inherited from prototype!
</pre>

					<h3>How Prototype Pollution Happens</h3>
					<p>Common vulnerable patterns:</p>
					
					<p><strong>1. Recursive Merge Functions:</strong></p>
					<pre>function merge(target, source) {
    for (let key in source) {
        if (typeof source[key] === 'object') {
            target[key] = merge(target[key] || {}, source[key]);
        } else {
            target[key] = source[key];
        }
    }
}

// Attacker input:
merge({}, JSON.parse('{\"__proto__\": {\"isAdmin\": true}}'));</pre>

					<p><strong>2. Property Assignment via Strings:</strong></p>
					<pre>function set(obj, path, value) {
    const keys = path.split('.');
    let current = obj;
    for (let i = 0; i < keys.length - 1; i++) {
        current = current[keys[i]];
    }
    current[keys[keys.length - 1]] = value;
}

// Attacker input:
set({}, '__proto__.isAdmin', true);</pre>

					<h3>Attack Vectors</h3>
					<ul>
						<li><strong>__proto__:</strong> Direct prototype reference</li>
						<li><strong>constructor.prototype:</strong> Via constructor property</li>
						<li><strong>Bracket notation:</strong> obj['__proto__']</li>
						<li><strong>JSON.parse:</strong> Malicious JSON input</li>
						<li><strong>Query strings:</strong> URL parameters parsed into objects</li>
					</ul>

					<h3>Example Payloads</h3>
					
					<p><strong>Basic Prototype Pollution:</strong></p>
					<pre>{\"__proto__\": {\"polluted\": \"yes\"}}</pre>

					<p><strong>Admin Privilege Escalation:</strong></p>
					<pre>{\"__proto__\": {\"isAdmin\": true, \"role\": \"admin\"}}</pre>

					<p><strong>Constructor Pollution:</strong></p>
					<pre>{\"constructor\": {\"prototype\": {\"isAdmin\": true}}}</pre>

					<p><strong>Nested Path:</strong></p>
					<pre>{\"user\": {\"__proto__\": {\"verified\": true}}}</pre>

					<p><strong>XSS via Prototype Pollution:</strong></p>
					<pre>{\"__proto__\": {\"innerHTML\": \"&lt;img src=x onerror=alert(1)&gt;\"}}</pre>

					<h3>Impact</h3>
					<ul>
						<li><strong>Privilege Escalation:</strong> Set isAdmin or role properties</li>
						<li><strong>Authentication Bypass:</strong> Pollute authentication checks</li>
						<li><strong>XSS:</strong> Inject malicious HTML/JavaScript</li>
						<li><strong>Denial of Service:</strong> Crash application with recursive structures</li>
						<li><strong>Remote Code Execution:</strong> In Node.js via child_process pollution</li>
						<li><strong>Logic Bypass:</strong> Modify application behavior</li>
					</ul>

					<br /><hr /><br />

					<h3>Objective</h3>
					<p>Your goal is to exploit prototype pollution to:</p>
					<ul>
						<li><strong>Low:</strong> Pollute Object.prototype with arbitrary properties</li>
						<li><strong>Medium:</strong> Bypass __proto__ blacklist using alternative methods</li>
						<li><strong>High:</strong> Bypass multiple blacklisted keywords</li>
						<li><strong>Impossible:</strong> Understand comprehensive protection mechanisms</li>
					</ul>

					<br /><hr /><br />

					<h3>Low Level</h3>
					<p>No validation on property names. Direct merge allows __proto__ pollution.</p>
					<p><em>Spoiler:</em> <span class=\"spoiler\">Use {\"__proto__\": {\"isAdmin\": true}} to pollute the prototype. Then click \"Check Admin Status\" to see the pollution effect.</span></p>

					<br />

					<h3>Medium Level</h3>
					<p>Blacklists __proto__ keyword but doesn't check other vectors.</p>
					<p><em>Spoiler:</em> <span class=\"spoiler\">Use {\"constructor\": {\"prototype\": {\"isAdmin\": true}}} to bypass the __proto__ filter. The constructor.prototype path still allows pollution.</span></p>

					<br />

					<h3>High Level</h3>
					<p>Blacklists __proto__, constructor, and prototype keywords.</p>
					<p><em>Spoiler:</em> <span class=\"spoiler\">This level is harder to bypass. In real scenarios, you might try Unicode escapes, bracket notation, or other parser quirks. The filtering is comprehensive but not perfect.</span></p>

					<br />

					<h3>Impossible Level</h3>
					<p>Comprehensive protection using multiple techniques:</p>
					<ul>
						<li><strong>Object.create(null):</strong> Creates objects without prototype</li>
						<li><strong>Whitelist validation:</strong> Only allows specific property names</li>
						<li><strong>No nested objects:</strong> Prevents recursive pollution</li>
						<li><strong>Object.defineProperty:</strong> Controlled property assignment</li>
						<li><strong>Map instead of objects:</strong> Avoids prototype chain</li>
						<li><strong>Object.freeze:</strong> Freezes Object.prototype</li>
					</ul>
					<p>This multi-layered approach provides defense in depth.</p>

					<br />

					<h3>Defense Strategies</h3>
					
					<h4>1. Use Object.create(null)</h4>
					<pre>// Objects without prototype
const safeObj = Object.create(null);
safeObj.__proto__ = 'test';  // Just a regular property, not pollution</pre>

					<h4>2. Use Map Instead of Objects</h4>
					<pre>const config = new Map();
config.set('key', 'value');  // No prototype pollution possible</pre>

					<h4>3. Freeze Prototypes</h4>
					<pre>Object.freeze(Object.prototype);
// Now Object.prototype is immutable</pre>

					<h4>4. JSON Schema Validation</h4>
					<pre>// Validate against strict schema
const Ajv = require('ajv');
const ajv = new Ajv();
const schema = {
    type: 'object',
    properties: {
        theme: {type: 'string'},
        lang: {type: 'string'}
    },
    additionalProperties: false
};</pre>

					<h4>5. Whitelist Properties</h4>
					<pre>const allowedKeys = ['theme', 'language'];
if (!allowedKeys.includes(key)) {
    throw new Error('Invalid property');
}</pre>

					<h4>6. Use hasOwnProperty</h4>
					<pre>// Only check own properties, not inherited
if (Object.prototype.hasOwnProperty.call(obj, key)) {
    // Safe to use
}</pre>

					<h4>7. Safe Merge Libraries</h4>
					<pre>// Use libraries with prototype pollution protection
const _ = require('lodash');
// Lodash 4.17.11+ has prototype pollution fixes</pre>

					<h3>Vulnerable Libraries</h3>
					<p>Historical vulnerabilities:</p>
					<ul>
						<li><strong>lodash &lt; 4.17.11:</strong> Vulnerable merge/defaults functions</li>
						<li><strong>jQuery &lt; 3.4.0:</strong> Vulnerable $.extend()</li>
						<li><strong>hoek &lt; 5.0.3:</strong> Vulnerable merge</li>
						<li><strong>minimist &lt; 1.2.3:</strong> Vulnerable argument parsing</li>
						<li><strong>merge &lt; 2.1.1:</strong> Vulnerable merge function</li>
					</ul>

					<h3>Testing Tips</h3>
					<ul>
						<li>Check if new empty objects inherit polluted properties</li>
						<li>Try both __proto__ and constructor.prototype</li>
						<li>Test with nested JSON structures</li>
						<li>Look for recursive merge/extend functions</li>
						<li>Check URL parameter parsing (qs, query-string libraries)</li>
						<li>Use browser console to inspect Object.prototype</li>
						<li>Try polluting common properties: isAdmin, authenticated, role</li>
					</ul>

					<h3>Real-World Examples</h3>
					<ul>
						<li><strong>Lodash (CVE-2019-10744):</strong> Prototype pollution via defaultsDeep</li>
						<li><strong>jQuery (CVE-2019-11358):</strong> Prototype pollution via $.extend</li>
						<li><strong>Kibana (CVE-2019-7609):</strong> RCE via prototype pollution</li>
						<li><strong>Ghost CMS (2020):</strong> Privilege escalation via prototype pollution</li>
					</ul>

					<br />

					<h3>References</h3>
					<ul>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/prototype-pollution', 'PortSwigger - Prototype Pollution' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://portswigger.net/daily-swig/prototype-pollution', 'The Daily Swig - Prototype Pollution' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://github.com/BlackFan/client-side-prototype-pollution', 'Client-Side Prototype Pollution Gadgets' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://book.hacktricks.xyz/pentesting-web/deserialization/nodejs-proto-prototype-pollution', 'HackTricks - Prototype Pollution' ); ?></li>
						<li><?php echo dvwaExternalLinkUrlGet( 'https://github.com/HoLyVieR/prototype-pollution-nsec18', 'Prototype Pollution Attack (NorthSec 2018)' ); ?></li>
					</ul>
				</div></td>
			</tr>
		</table>
	</div>
</div>
