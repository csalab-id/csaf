<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: XML External Entity (XXE)' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'xxe';
$page[ 'help_button' ]   = 'xxe';
$page[ 'source_button' ] = 'xxe';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/xxe/source/{$vulnerabilityFile}";

$messagesHtml  = "<div class=\"info\">Enter XML data below. The parser will extract and display the content.</div>";
$messagesHtml .= "<div class=\"warning\">Try injecting XML External Entity (XXE) to read server files!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: XML External Entity (XXE)</h1>

	{$messagesHtml}

	<form name=\"xxe\" method=\"POST\">
		<p>
			XML Data:<br />
			<textarea name=\"xml\" cols=\"80\" rows=\"15\" placeholder='<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<user>
    <name>John Doe</name>
    <email>john@example.com</email>
</user>'>" . (isset($_POST['xml']) ? htmlspecialchars($_POST['xml']) : '') . "</textarea>
		</p>
		<p>
			<input type=\"submit\" value=\"Parse XML\" name=\"submit\" />
		</p>
	</form>
	{$xxeHtml}
	<br />
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/vulnerabilities/XML_External_Entity_(XXE)_Processing', 'OWASP - XXE' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/xxe', 'PortSwigger - XXE' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/XML_External_Entity_Prevention_Cheat_Sheet.html', 'OWASP XXE Prevention Cheat Sheet' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
