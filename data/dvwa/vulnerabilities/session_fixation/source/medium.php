<?php

$fixationHtml = "";

if( isset( $_GET['PHPSESSID'] ) ) {
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: orange;\">Session fixation via URL blocked.</p></div>";
}

if( isset( $_GET['logout'] ) ) {
	unset($_SESSION['fixation_user']);
	unset($_SESSION['fixation_logged_in']);
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p>Logged out successfully.</p></div>";
}

if( isset( $_POST['login'] ) ) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	if( $username === 'admin' && $password === 'password' ) {
		session_regenerate_id(true);
		
		$_SESSION['fixation_user'] = $username;
		$_SESSION['fixation_logged_in'] = true;
		
		$fixationHtml .= "<div class=\"vulnerable_code_area\">";
		$fixationHtml .= "<h3>✓ Login Successful!</h3>";
		$fixationHtml .= "<p>Welcome, " . htmlspecialchars($username) . "!</p>";
		$fixationHtml .= "<p><a href=\"?logout=1\">Logout</a></p>";
		$fixationHtml .= "</div>";
	} else {
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Invalid credentials.</p></div>";
	}
}

if( !isset($_SESSION['fixation_logged_in']) ) {
	$fixationHtml .= "
<div class=\"vulnerable_code_area\">
	<form method=\"POST\">
		<fieldset style=\"max-width: 400px;\">
			<h2>Login</h2>
			<p>
				<label>Username:</label><br>
				<input type=\"text\" name=\"username\" value=\"\" />
			</p>
			<p>
				<label>Password:</label><br>
				<input type=\"password\" name=\"password\" value=\"\" />
			</p>
			<p>
				<button type=\"submit\" name=\"login\">Login</button>
			</p>
		</fieldset>
	</form>
</div>";
}

?>
