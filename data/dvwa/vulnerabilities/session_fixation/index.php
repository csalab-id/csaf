<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../../' );
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

$messagesHtml  = "<div class=\"info\">This page simulates a login system vulnerable to session fixation attacks.</div>";
$messagesHtml .= "<div class=\"warning\">Try fixing a session ID before authentication to hijack the session!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Session Fixation</h1>

	{$messagesHtml}

	<div style=\"margin: 20px 0; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;\">
		<h3>Current Session Info</h3>
		<p><strong>Session ID:</strong> <code>" . htmlspecialchars(session_id()) . "</code></p>
		<p><strong>User:</strong> " . (isset($_SESSION['fixation_user']) ? htmlspecialchars($_SESSION['fixation_user']) : 'Not logged in') . "</p>
		<p><strong>Logged in:</strong> " . (isset($_SESSION['fixation_logged_in']) ? 'Yes' : 'No') . "</p>
	</div>

	{$fixationHtml}

	<div style=\"margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;\">
		<h4>Attack Scenario</h4>
		<p><strong>Step 1:</strong> Attacker visits the site and gets a session ID</p>
		<p><strong>Step 2:</strong> Attacker sends victim a link with the fixed session ID:<br>
		<code>http://dvwa.lab/vulnerabilities/session_fixation/?PHPSESSID=attacker_session_id</code></p>
		<p><strong>Step 3:</strong> Victim clicks the link and logs in with their credentials</p>
		<p><strong>Step 4:</strong> Attacker uses the same session ID to access victim's account</p>
		<br>
		<p><em>Note: To test, open an incognito/private window and use the URL with a session ID parameter.</em></p>
	</div>
	<br />
	
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
