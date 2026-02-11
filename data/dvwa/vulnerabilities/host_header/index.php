<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Host Header Injection' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'host_header';
$page[ 'help_button' ]   = 'host_header';
$page[ 'source_button' ] = 'host_header';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/host_header/source/{$vulnerabilityFile}";

$messagesHtml  = "<div class=\"info\">Host Header Injection occurs when applications trust the HTTP Host header without validation.</div>";
$messagesHtml .= "<div class=\"warning\">This can lead to password reset poisoning, cache poisoning, and SSRF attacks!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Host Header Injection</h1>

	{$messagesHtml}

	<div style=\"margin: 20px 0; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;\">
		<h3>Current Request Info</h3>
		<p><strong>Host Header:</strong> <code>" . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Not set') . "</code></p>
		<p><strong>Server Name:</strong> <code>" . htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'Not set') . "</code></p>
		<p><strong>Request URI:</strong> <code>" . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Not set') . "</code></p>
	</div>

	{$hostHeaderHtml}
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/host-header', 'PortSwigger - Host Header Attacks' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/17-Testing_for_Host_Header_Injection', 'OWASP - Host Header Injection Testing' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cwe.mitre.org/data/definitions/644.html', 'CWE-644: Improper Neutralization of HTTP Headers' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
