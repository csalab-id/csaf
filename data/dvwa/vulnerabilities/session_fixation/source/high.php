<?php

$fixationHtml = "";

// Handle logout
if( isset( $_GET['logout'] ) ) {
	// Regenerate session on logout too
	session_regenerate_id(true);
	unset($_SESSION['fixation_user']);
	unset($_SESSION['fixation_logged_in']);
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p>Logged out successfully.</p></div>";
}

// Handle login
if( isset( $_POST['login'] ) ) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	if( $username === 'admin' && $password === 'password' ) {
		// Better: Regenerate session ID after successful login
		$old_session_id = session_id();
		session_regenerate_id(true);
		$new_session_id = session_id();
		
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
