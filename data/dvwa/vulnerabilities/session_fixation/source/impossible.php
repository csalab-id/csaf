<?php

$fixationHtml = "";

// SECURE: Multiple layers of protection

// Check for suspicious session manipulation
if( isset($_GET['PHPSESSID']) || isset($_GET['session_id']) ) {
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">⚠️ Session fixation attempt detected and blocked!</p></div>";
	// Regenerate to prevent any potential fixation
	session_regenerate_id(true);
}

// Handle logout
if( isset( $_GET['logout'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	// Completely destroy session
	session_regenerate_id(true);
	$_SESSION = array();
	
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-3600, '/');
	}
	
	session_destroy();
	session_start(); // Start new clean session
	
	$fixationHtml .= "<div class=\"vulnerable_code_area\"><p>Logged out securely.</p></div>";
}

// Handle login
if( isset( $_POST['login'] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	if( $username === 'admin' && $password === 'password' ) {
		// SECURE: Multiple protection layers
		
		// 1. Regenerate session ID
		$old_session_id = session_id();
		session_regenerate_id(true);
		$new_session_id = session_id();
		
		// 2. Set session data
		$_SESSION['fixation_user'] = $username;
		$_SESSION['fixation_logged_in'] = true;
		
		// 3. Bind session to IP and User-Agent
		$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		
		// 4. Set session timeout
		$_SESSION['login_time'] = time();
		
		// 5. Use secure cookie settings
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params([
			'lifetime' => $cookieParams['lifetime'],
			'path' => $cookieParams['path'],
			'domain' => $cookieParams['domain'],
			'secure' => false, // Set true in production with HTTPS
			'httponly' => true,
			'samesite' => 'Strict'
		]);
		
		$fixationHtml .= "<div class=\"vulnerable_code_area\">";
		$fixationHtml .= "<h3 style=\"color: green;\">✓ Secure Login Successful!</h3>";
		$fixationHtml .= "<p>Welcome, " . htmlspecialchars($username) . "!</p>";
		$fixationHtml .= "<p><a href=\"?logout=1&user_token=" . $_SESSION['session_token'] . "\">Logout</a></p>";
		$fixationHtml .= "</div>";
	} else {
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Invalid credentials.</p></div>";
	}
}

// Validate existing session
if( isset($_SESSION['fixation_logged_in']) ) {
	// Check IP binding
	if( isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] ) {
		session_destroy();
		session_start();
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: red;\">Session hijacking detected! IP address mismatch.</p></div>";
	}
	
	// Check timeout (30 minutes)
	if( isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800) ) {
		session_destroy();
		session_start();
		$fixationHtml .= "<div class=\"vulnerable_code_area\"><p style=\"color: orange;\">Session expired. Please login again.</p></div>";
	}
}

if( !isset($_SESSION['fixation_logged_in']) ) {
	generateSessionToken();
	
	$fixationHtml .= "
	<form method=\"POST\">
		<fieldset style=\"max-width: 400px;\">
			<legend>Secure Login</legend>
			<p>
				<label>Username:</label><br>
				<input type=\"text\" name=\"username\" value=\"admin\" />
			</p>
			<p>
				<label>Password:</label><br>
				<input type=\"password\" name=\"password\" value=\"password\" />
			</p>
			<input type=\"hidden\" name=\"user_token\" value=\"" . $_SESSION['session_token'] . "\" />
			<p>
				<button type=\"submit\" name=\"login\">Login</button>
			</p>
		</fieldset>
	</form>";
}

?>
