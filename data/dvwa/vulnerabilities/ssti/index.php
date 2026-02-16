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

$method = 'GET';
$vulnerabilityFile = '';
switch( dvwaSecurityLevelGet() ) {
	case 'low':
		$vulnerabilityFile = 'low.php';
		$method = 'GET';
		break;
	case 'medium':
		$vulnerabilityFile = 'medium.php';
		$method = 'POST';
		break;
	case 'high':
		$vulnerabilityFile = 'high.php';
		$method = 'POST';
		break;
	case 'impossible':
		$vulnerabilityFile = 'impossible.php';
		$method = 'POST';
		break;
	default:
		$vulnerabilityFile = 'low.php';
		$method = 'GET';
		break;
}

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/ssti/source/{$vulnerabilityFile}";

$currentName = '';
if( $method == 'GET' && isset($_GET['name']) ) {
	$currentName = htmlspecialchars($_GET['name']);
} elseif( $method == 'POST' && isset($_POST['name']) ) {
	$currentName = htmlspecialchars($_POST['name']);
}

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Server-Side Template Injection (SSTI)</h1>

	<div class=\"vulnerable_code_area\">
	<form name=\"ssti\" method=\"{$method}\">
		<p>
			Your Name:<br />
			<input type=\"text\" name=\"name\" size=\"50\" placeholder=\"John Doe\" value=\"{$currentName}\" />
		</p>
		<p>
			<input type=\"submit\" value=\"Generate Greeting\" name=\"submit\" />";

if( $vulnerabilityFile == 'high.php' || $vulnerabilityFile == 'impossible.php' )
	$page[ 'body' ] .= tokenField();

$page[ 'body' ] .= "
		</p>
	</form>
	</div>

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
