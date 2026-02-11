<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Session Fixation' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'session_fixation';
$page[ 'help_button' ]   = 'session_fixation';
$page[ 'source_button' ] = 'session_fixation';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/session_fixation/source/{$vulnerabilityFile}";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Session Fixation</h1>

	<div class=\"vulnerable_code_area\">
		<h3>Current Session Info</h3>
		<p><strong>Session ID:</strong> <code>" . htmlspecialchars(session_id()) . "</code></p>
		<p><strong>User:</strong> " . (isset($_SESSION['fixation_user']) ? htmlspecialchars($_SESSION['fixation_user']) : 'Not logged in') . "</p>
		<p><strong>Logged in:</strong> " . (isset($_SESSION['fixation_logged_in']) ? 'Yes' : 'No') . "</p>
	</div>

	{$fixationHtml}

	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/Session_fixation', 'OWASP - Session Fixation' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/authentication/other-mechanisms', 'PortSwigger - Authentication Vulnerabilities' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html', 'OWASP Session Management' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
