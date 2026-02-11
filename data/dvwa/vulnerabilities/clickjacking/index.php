<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ]   = 'Vulnerability: Clickjacking' . $page[ 'title_separator' ].$page[ 'title' ];
$page[ 'page_id' ] = 'clickjacking';
$page[ 'help_button' ]   = 'clickjacking';
$page[ 'source_button' ] = 'clickjacking';

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

require_once DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/clickjacking/source/{$vulnerabilityFile}";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Clickjacking (UI Redressing)</h1>

	<div class=\"vulnerable_code_area\">
		<h3>Account Settings</h3>
		<p>Manage your account preferences and security settings.</p>
		
		<form name=\"clickjacking\" method=\"POST\">
			<div style=\"margin: 15px 0;\">
				<label>
					<input type=\"checkbox\" name=\"public_profile\" value=\"1\" /> Make my profile public
				</label>
			</div>
			<div style=\"margin: 15px 0;\">
				<label>
					<input type=\"checkbox\" name=\"share_data\" value=\"1\" /> Share my data with third parties
				</label>
			</div>
			<div style=\"margin: 15px 0;\">
				<label>
					<input type=\"checkbox\" name=\"admin_access\" value=\"1\" /> Grant admin access to external users
				</label>
			</div>
			<div style=\"margin: 20px 0;\">
				<button type=\"submit\" name=\"submit\">
					Update Settings
				</button>
			</div>
		</form>
	</div>

	{$clickjackingHtml}
	
	<h2>More Information</h2>
	<ul>
		<li>" . dvwaExternalLinkUrlGet( 'https://owasp.org/www-community/attacks/Clickjacking', 'OWASP - Clickjacking' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://portswigger.net/web-security/clickjacking', 'PortSwigger - Clickjacking' ) . "</li>
		<li>" . dvwaExternalLinkUrlGet( 'https://cheatsheetseries.owasp.org/cheatsheets/Clickjacking_Defense_Cheat_Sheet.html', 'OWASP Clickjacking Defense' ) . "</li>
	</ul>
</div>
";

dvwaHtmlEcho( $page );

?>
