<?php

$fixationHtml = "";

if( isset($_GET['PHPSESSID']) || isset($_GET['session_id']) ) {
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Session fixation attempt blocked.</p></div>";
	session_regenerate_id(true);
}

if( isset( $_GET['logout'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	session_regenerate_id(true);
	$_SESSION = array();
	
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-3600, '/');
	}
	
	session_destroy();
	session_start();
	
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p>Logged out successfully.</p></div>";
}

if( isset( $_POST['login'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	if( $username === 'admin' && $password === 'password' ) {
		$old_session_id = session_id();
		session_regenerate_id(true);
		$new_session_id = session_id();

		$_SESSION['fixation_user'] = $username;
		$_SESSION['fixation_logged_in'] = true;
		$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$_SESSION['login_time'] = time();

		$cookieParams = session_get_cookie_params();
		session_set_cookie_params([
			'lifetime' => $cookieParams['lifetime'],
			'path' => $cookieParams['path'],
			'domain' => $cookieParams['domain'],
			'secure' => false,
			'httponly' => true,
			'samesite' => 'Strict'
		]);
		
		$fixationHtml .= "<div class=\"vulnerable_code_area\">";
		$fixationHtml .= "<h3>✓ Login Successful!</h3>";
		$fixationHtml .= "<p>Welcome, " . htmlspecialchars($username) . "!</p>";
		$fixationHtml .= "<p><a href=\"?logout=1&user_token=" . $_SESSION['session_token'] . "\">Logout</a></p>";
		$fixationHtml .= "</div>";
	} else {
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Invalid credentials.</p></div>";
	}
}

if( isset($_SESSION['fixation_logged_in']) ) {
	if( isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] ) {
		session_destroy();
		session_start();
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Session terminated.</p></div>";
	}

	if( isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800) ) {
		session_destroy();
		session_start();
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Session expired.</p></div>";
	}
}

if( !isset($_SESSION['fixation_logged_in']) ) {
	generateSessionToken();
	
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
			<input type=\"hidden\" name=\"user_token\" value=\"" . $_SESSION['session_token'] . "\" />
			<p>
				<button type=\"submit\" name=\"login\">Login</button>
			</p>
		</fieldset>
	</form>
</div>";
}

?>
