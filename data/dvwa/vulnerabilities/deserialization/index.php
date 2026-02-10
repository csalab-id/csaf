<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Insecure Deserialization' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'deserialization';
$page[ 'help_button' ]   = 'deserialization';
$page[ 'source_button' ] = 'deserialization';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/deserialization/source/{$vulnerabilityFile}";

$messagesHtml  = "<div class=\"info\">This page allows you to save and load user preferences.</div>";
$messagesHtml .= "<div class=\"warning\">Try injecting malicious serialized objects to execute code!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Insecure Deserialization</h1>

	{$messagesHtml}

	<div style=\"margin-bottom: 20px;\">
		<h3>Save Preferences</h3>
		<form name=\"save_prefs\" method=\"POST\">
			<p>
				Theme: 
				<select name=\"theme\">
					<option value=\"light\">Light</option>
					<option value=\"dark\">Dark</option>
					<option value=\"blue\">Blue</option>
				</select>
			</p>
			<p>
				Language: 
				<select name=\"language\">
					<option value=\"en\">English</option>
					<option value=\"es\">Spanish</option>
					<option value=\"fr\">French</option>
				</select>
			</p>
			<p>
				<input type=\"submit\" value=\"Save Preferences\" name=\"save\" />
			</p>
		</form>
	</div>

	<div style=\"margin-bottom: 20px;\">
		<h3>Load Preferences</h3>
		<form name=\"load_prefs\" method=\"POST\">
			<p>
				Serialized Data:<br />
				<textarea name=\"data\" cols=\"80\" rows=\"5\" placeholder=\"Paste serialized data here...\">" . (isset($_POST['data']) ? htmlspecialchars($_POST['data']) : '') . "</textarea>
			</p>
			<p>
				<input type=\"submit\" value=\"Load Preferences\" name=\"load\" />
			</p>
		</form>
	</div>

	{$deserializeHtml}
	<br />
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/vulnerabilities/Deserialization_of_untrusted_data', 'OWASP - Deserialization' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/deserialization', 'PortSwigger - Insecure Deserialization' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Deserialization_Cheat_Sheet.html', 'OWASP Deserialization Prevention' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-top-ten/2017/A8_2017-Insecure_Deserialization', 'OWASP Top 10 A8:2017' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
