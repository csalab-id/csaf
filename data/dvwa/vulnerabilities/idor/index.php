<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Insecure Direct Object Reference (IDOR)' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'idor';
$page[ 'help_button' ]   = 'idor';
$page[ 'source_button' ] = 'idor';
dvwaDatabaseConnect();

$method            = 'GET';
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
	default:
		$vulnerabilityFile = 'impossible.php';
		$method = 'POST';
		break;
}

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/idor/source/{$vulnerabilityFile}";

$default_user_id = '2';

$encoded_user_id = base64_encode( $default_user_id );

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Insecure Direct Object Reference (IDOR)</h1>

	<div class=\"vulnerable_code_area\">
		<h2>Login</h2>

		<form action=\"#\" method=\"{$method}\">
			Username:<br />
			<input type=\"text\" name=\"username\" value=\"gordonb\"><br />
			Password:<br />
			<input type=\"password\" AUTOCOMPLETE=\"off\" name=\"password\" value=\"abc123\"><br />\n";

if( dvwaSecurityLevelGet() == 'low' ) {
	$page[ 'body' ] .= "<input type=\"hidden\" name=\"user_id\" value=\"{$default_user_id}\">";
} elseif( dvwaSecurityLevelGet() == 'medium' ) {
	$page[ 'body' ] .= "<input type=\"hidden\" name=\"user_id\" value=\"{$default_user_id}\">";
} elseif( dvwaSecurityLevelGet() == 'high' ) {
	$page[ 'body' ] .= "<input type=\"hidden\" name=\"user_id\" value=\"{$encoded_user_id}\">";
}

$page[ 'body' ] .= "			<br />
			<input type=\"submit\" value=\"Login\" name=\"Login\">\n";

if( $vulnerabilityFile == 'high.php' || $vulnerabilityFile == 'impossible.php' )
	$page[ 'body' ] .= "			" . tokenField();

$page[ 'body' ] .= "
		</form>
		{$html}
	</div>

	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/05-Authorization_Testing/04-Testing_for_Insecure_Direct_Object_References', 'OWASP - Testing for IDOR' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/access-control/idor', 'PortSwigger - IDOR' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Insecure_Direct_Object_Reference_Prevention_Cheat_Sheet.html', 'OWASP IDOR Prevention' ) . "</li>
	</ul>
</div>\n";

dvwaHtmlEcho( $page );

?>
