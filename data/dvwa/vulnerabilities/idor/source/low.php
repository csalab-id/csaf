<?php

$html = "";

if( isset( $_GET[ 'Login' ] ) ) {
	$username = $_GET[ 'username' ];
	$password = $_GET[ 'password' ];
	$user_id  = $_GET[ 'user_id' ];

	if( !empty( $username ) && !empty( $password ) && !empty( $user_id ) ) {
		$query  = "SELECT * FROM `users` WHERE user = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $username) . "' AND password = '" . md5($password) . "';";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);

		if( $result && mysqli_num_rows( $result ) == 1 ) {
			$query2  = "SELECT * FROM `users` WHERE user_id = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $user_id) . "';";
			$result2 = mysqli_query($GLOBALS["___mysqli_ston"], $query2);

			if( $result2 && mysqli_num_rows( $result2 ) == 1 ) {
				$row = mysqli_fetch_assoc( $result2 );
				$loggedInAs = $row['user'];
				$avatar = $row['avatar'];

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
			}
		} else {
			$html .= "<pre><br />Invalid username or password.</pre>";
		}
	} else {
		$html .= "<pre><br />Please fill in all fields.</pre>";
	}
}

?>
