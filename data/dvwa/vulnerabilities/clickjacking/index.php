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

$messagesHtml  = "<div class=\"info\">This page demonstrates a sensitive action that could be vulnerable to clickjacking attacks.</div>";
$messagesHtml .= "<div class=\"warning\">Try embedding this page in an iframe on a malicious site to perform UI redressing!</div>";

$page[ 'body' ] .= "
<div class=\"body_padded\">
	<h1>Vulnerability: Clickjacking (UI Redressing)</h1>

	{$messagesHtml}

	<div style=\"margin: 20px 0; padding: 20px; background: #f9f9f9; border: 2px solid #ddd; border-radius: 5px;\">
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
				<button type=\"submit\" name=\"submit\" style=\"padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px;\">
					Update Settings
				</button>
			</div>
		</form>
	</div>

	{$clickjackingHtml}

	<div style=\"margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;\">
		<h4>Demo Attack Page</h4>
		<p>To test clickjacking, create an HTML file with the following code and open it in your browser:</p>
		<pre style=\"background: #f5f5f5; padding: 10px; overflow-x: auto;\">&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Win a Free Prize!&lt;/title&gt;
    &lt;style&gt;
        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.0;  /* Make invisible - change to 0.5 to see the overlay */
            z-index: 2;
        }
        .fake-content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 100px 20px;
        }
        button {
            padding: 20px 40px;
            font-size: 24px;
            background: #ff5722;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class=\"fake-content\"&gt;
        &lt;h1&gt;🎉 Congratulations! 🎉&lt;/h1&gt;
        &lt;p&gt;You've won a FREE iPhone! Click below to claim:&lt;/p&gt;
        &lt;button&gt;Click Here to Claim Prize&lt;/button&gt;
    &lt;/div&gt;
    &lt;iframe src=\"http://dvwa.lab/vulnerabilities/clickjacking/\"&gt;&lt;/iframe&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
		<p><em>Note: Adjust the iframe positioning to align the hidden button with your fake button.</em></p>
	</div>
	<br />
	
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
