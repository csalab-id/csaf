<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Remote Code Execution (RCE)' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'rce';
$page[ 'help_button' ]   = 'rce';
$page[ 'source_button' ] = 'rce';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/rce/source/{$vulnerabilityFile}";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Remote Code Execution (RCE)</h1>

	<div class=\"vulnerable_code_area\">
	<form name=\"rce\" method=\"GET\">
		<p>
			Your Name:<br />
			<input type=\"text\" name=\"name\" size=\"50\" placeholder=\"John Doe\" value=\"" . (isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '') . "\" />
		</p>
		<p>
			<input type=\"submit\" value=\"Generate Greeting\" name=\"submit\" />
		</p>
	</form>
	</div>

	{$rceHtml}
	<br />
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/Code_Injection', 'OWASP - Code Injection' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://www.php.net/manual/en/function.eval.php', 'PHP Manual - eval() Function' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection', 'OWASP - PHP Object Injection' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html', 'OWASP - PHP Configuration' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
