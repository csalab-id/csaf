<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Prototype Pollution' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'prototype';
$page[ 'help_button' ]   = 'prototype';
$page[ 'source_button' ] = 'prototype';

dvwaDatabaseConnect();

$vulnerabilityFile = '';
switch( dvwaSecurityLevelGet() ) {
	case 'low':
		$vulnerabilityFile = 'low.php';
		break;
	case 'medium':
		$vulnerabilityFile = 'medium.php';
		break;
	case 'high':
		$vulnerabilityFile = 'high.php';
		break;
	case 'impossible':
		$vulnerabilityFile = 'impossible.php';
		break;
	default:
		$vulnerabilityFile = 'low.php';
		break;
}

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/prototype/source/{$vulnerabilityFile}";

$messagesHtml  = "<div class=\"info\">Configure user preferences using JSON data.</div>";
$messagesHtml .= "<div class=\"warning\">Try polluting the prototype chain to modify object behavior!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Prototype Pollution</h1>

	{$messagesHtml}

	<div style=\"margin: 20px 0;\">
		<h3>User Preferences</h3>
		<form id=\"preferencesForm\">
			<p>
				Enter JSON configuration:<br />
				<textarea id=\"jsonInput\" cols=\"80\" rows=\"10\" placeholder='{\"theme\": \"dark\", \"language\": \"en\"}'>{\"theme\": \"light\", \"language\": \"en\"}</textarea>
			</p>
			<p>
				<button type=\"button\" onclick=\"applyPreferences()\">Apply Preferences</button>
			</p>
		</form>
	</div>

	<div id=\"result\" style=\"margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; display: none;\">
		<h3>Applied Configuration:</h3>
		<pre id=\"configOutput\"></pre>
	</div>

	<div id=\"testArea\" style=\"margin: 20px 0; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;\">
		<h3>Test Area</h3>
		<p>After polluting the prototype, try these tests:</p>
		<button onclick=\"testNewObject()\">Create New Object</button>
		<button onclick=\"testAdmin()\">Check Admin Status</button>
		<button onclick=\"testSanitize()\">Test Sanitization</button>
		<div id=\"testOutput\" style=\"margin-top: 10px; padding: 10px; background: white; border-radius: 3px;\"></div>
	</div>

	<div style=\"margin: 20px 0; padding: 15px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 5px;\">
		<h4>Example Payloads</h4>
		<p><strong>Basic Prototype Pollution:</strong></p>
		<pre>{\"__proto__\": {\"polluted\": \"true\"}}</pre>
		
		<p><strong>Admin Access:</strong></p>
		<pre>{\"theme\": \"dark\", \"__proto__\": {\"isAdmin\": true}}</pre>
		
		<p><strong>Constructor Pollution:</strong></p>
		<pre>{\"constructor\": {\"prototype\": {\"isAdmin\": true}}}</pre>
	</div>

	{$prototypeHtml}
	<br />
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/prototype-pollution', 'PortSwigger - Prototype Pollution' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/daily-swig/prototype-pollution', 'The Daily Swig - Prototype Pollution' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://github.com/BlackFan/client-side-prototype-pollution', 'Client-Side Prototype Pollution' ) . "</li>
	</ul>
</div>

<script>
{$vulnerabilityScript}

function testNewObject() {
	const obj = {};
	const output = document.getElementById('testOutput');
	output.innerHTML = '<strong>New Empty Object Properties:</strong><br>';
	output.innerHTML += 'obj.polluted = ' + obj.polluted + '<br>';
	output.innerHTML += 'obj.isAdmin = ' + obj.isAdmin + '<br>';
	output.innerHTML += 'obj.sanitized = ' + obj.sanitized + '<br>';
}

function testAdmin() {
	const user = {name: 'john'};
	const output = document.getElementById('testOutput');
	output.innerHTML = '<strong>User Object:</strong><br>';
	output.innerHTML += 'user.name = ' + user.name + '<br>';
	output.innerHTML += 'user.isAdmin = ' + user.isAdmin + '<br>';
	
	if (user.isAdmin) {
		output.innerHTML += '<span style=\"color: red; font-weight: bold;\">⚠️ Admin access granted via prototype pollution!</span>';
	} else {
		output.innerHTML += '<span style=\"color: green;\">✓ No admin access</span>';
	}
}

function testSanitize() {
	const input = '<script>alert(\"XSS\")</script>';
	const config = {};
	const output = document.getElementById('testOutput');
	
	// If prototype is polluted with sanitized: false, this could bypass sanitization
	const shouldSanitize = config.sanitized !== false;
	
	output.innerHTML = '<strong>Sanitization Test:</strong><br>';
	output.innerHTML += 'Input: ' + escapeHtml(input) + '<br>';
	output.innerHTML += 'config.sanitized = ' + config.sanitized + '<br>';
	output.innerHTML += 'Should sanitize: ' + shouldSanitize + '<br>';
	
	if (!shouldSanitize) {
		output.innerHTML += '<span style=\"color: red; font-weight: bold;\">⚠️ Sanitization bypassed via prototype pollution!</span>';
	}
}

function escapeHtml(text) {
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}
</script>
";

dvwaHtmlEcho( $page );

?>
