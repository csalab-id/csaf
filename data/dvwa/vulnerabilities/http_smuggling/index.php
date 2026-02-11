<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: HTTP Request Smuggling' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'http_smuggling';
$page[ 'help_button' ]   = 'http_smuggling';
$page[ 'source_button' ] = 'http_smuggling';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/http_smuggling/source/{$vulnerabilityFile}";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: HTTP Request Smuggling</h1>

	{$smugglingHtml}
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/request-smuggling', 'PortSwigger - HTTP Request Smuggling' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/HTTP_Request_Smuggling', 'OWASP - HTTP Request Smuggling' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cwe.mitre.org/data/definitions/444.html', 'CWE-444: HTTP Request Smuggling' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.rfcreader.com/#rfc7230', 'RFC 7230 - HTTP/1.1 Message Syntax' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
