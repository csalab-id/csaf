<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Server-Side Template Injection (SSTI)' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'ssti';
$page[ 'help_button' ]   = 'ssti';
$page[ 'source_button' ] = 'ssti';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/ssti/source/{$vulnerabilityFile}";

$messagesHtml  = "<div class=\"info\">Enter your name to generate a personalized greeting message.</div>";
$messagesHtml .= "<div class=\"warning\">Try injecting template syntax to execute code on the server!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Server-Side Template Injection (SSTI)</h1>

	{$messagesHtml}

	<form name=\"ssti\" method=\"GET\">
		<p>
			Your Name:<br />
			<input type=\"text\" name=\"name\" size=\"50\" placeholder=\"John Doe\" value=\"" . (isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '') . "\" />
		</p>
		<p>
			<input type=\"submit\" value=\"Generate Greeting\" name=\"submit\" />
		</p>
	</form>
	{$sstiHtml}
	<br />
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/18-Testing_for_Server-side_Template_Injection', 'OWASP - Server-Side Template Injection' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/research/server-side-template-injection', 'PortSwigger - SSTI Research' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/server-side-template-injection', 'PortSwigger - SSTI' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://book.hacktricks.xyz/pentesting-web/ssti-server-side-template-injection', 'HackTricks - SSTI' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
