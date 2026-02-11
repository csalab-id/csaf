<?php

$fixationHtml = "";

// VULNERABLE: Accepts session ID from URL parameter
if( isset( $_GET['PHPSESSID'] ) ) {
	session_id( $_GET['PHPSESSID'] );
}

// Handle logout
if( isset( $_GET['logout'] ) ) {
	unset($_SESSION['fixation_user']);
	unset($_SESSION['fixation_logged_in']);
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p>Logged out successfully.</p></div>";
}

// Handle login
if( isset( $_POST['login'] ) ) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	// Simple authentication (demo purposes)
	if( $username === 'admin' && $password === 'password' ) {
		// VULNERABLE: Session ID is NOT regenerated after login
		$_SESSION['fixation_user'] = $username;
		$_SESSION['fixation_logged_in'] = true;
		
		$fixationHtml .= "<div class=\"vulnerable_code_area\">";
		$fixationHtml .= "<h3>✓ Login Successful!</h3>";
		$fixationHtml .= "<p>Welcome, " . htmlspecialchars($username) . "!</p>";
		$fixationHtml .= "<p><a href=\"?logout=1\">Logout</a></p>";
		$fixationHtml .= "</div>";
	} else {
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Invalid credentials. Try admin/password</p></div>";
	}
}

if( !isset($_SESSION['fixation_logged_in']) ) {
	$fixationHtml .= "
	<form method=\"POST\">
		<fieldset style=\"max-width: 400px;\">
			<legend>Login</legend>
			<p>
				<label>Username:</label><br>
				<input type=\"text\" name=\"username\" value=\"admin\" />
			</p>
			<p>
				<label>Password:</label><br>
				<input type=\"password\" name=\"password\" value=\"password\" />
			</p>
			<p>
				<button type=\"submit\" name=\"login\">Login</button>
			</p>
		</fieldset>
	</form>";
}

?>
