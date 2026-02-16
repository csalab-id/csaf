<?php

$html = "";

if( isset( $_POST[ 'Login' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	$username = $_POST[ 'username' ];
	$password = $_POST[ 'password' ];

	if( !empty( $username ) && !empty( $password ) ) {
		$username = stripslashes( $username );
		$username = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $username );
		$password = stripslashes( $password );
		$password = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $password );
		$password = md5( $password );

		$query  = "SELECT * FROM `users` WHERE user = '{$username}' AND password = '{$password}';";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);

		if( $result && mysqli_num_rows( $result ) == 1 ) {
			$row = mysqli_fetch_assoc( $result );
			$loggedInAs = $row['user'];
			$avatar = $row['avatar'];

			$_SESSION['idor_user_id'] = $row['user_id'];
			$_SESSION['idor_username'] = $loggedInAs;

			$html .= "<div class='vulnerable_code_area'>";
			$html .= "<h2>Login Successful!</h2>";
			$html .= "<p>Welcome! You are logged in as: <strong>{$loggedInAs}</strong></p>";

			if( $loggedInAs == 'admin' ) {
				$html .= "<div style='background-color: #90EE90; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
				$html .= "<h3>🎉 Admin Access Granted!</h3>";
				$html .= "<p>You have successfully accessed the admin panel.</p>";
				$html .= "</div>";
			}
			
			$html .= "<img src='{$avatar}' style='max-width: 100px;' />";
			$html .= "</div>";
		} else {
			sleep( rand( 2, 4 ) );
			$html .= "<pre><br />Invalid username or password.</pre>";
		}
	} else {
		$html .= "<pre><br />Please fill in all fields.</pre>";
	}
}

generateSessionToken();

?>
