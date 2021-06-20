<?php  
if (isset($_POST['update_details'])) {

	$firstName = $_POST['first_name'];
	$lastName = $_POST['last_name'];
	$email = $_POST['email'];

	$emailCheck = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
	$row = mysqli_fetch_array($emailCheck);

	$matchedUser = $row['username'];
	
	if ($matchedUser == "" || $matchedUser == $userLoggedIn) {
		$query = mysqli_query($conn, "UPDATE users SET first_name = '$firstName', last_name = '$lastName', email = '$email' WHERE username = '$userLoggedIn'");
		$message = "Details updated!<br><br>";
	} else {
		$message = "That email is already in use!<br><br>";
	}
} else {
	$message = "";
}

if (isset($_POST['update_password'])) {

	$oldPassword = strip_tags($_POST['old_password']);
	$newPassword1 = strip_tags($_POST['new_password_1']);
	$newPassword2 = strip_tags($_POST['new_password_2']);

	$passwordQuery = mysqli_query($conn, "SELECT password FROM users WHERE username = '$userLoggedIn'");
	$row = mysqli_fetch_array($passwordQuery);
	$dbPassword = $row['password'];

	if (md5($oldPassword) == $dbPassword) {

		if ($newPassword1 == $newPassword2) {

			if (strlen($newPassword1) <= 4) {
				$passwordMessage = "Sorry, your password must be greater than 4 characters!<br><br>";
			} else {
				$newPasswordMD5 = md5($newPassword1);
				$passwordQuery = mysqli_query($conn, "UPDATE users SET password = '$newPasswordMD5' WHERE username = '$userLoggedIn'");
				$passwordMessage = "Password has been changed!<br><br>";
			}

		} else {
			$passwordMessage = "Your new passwords don't match!<br><br>";
		}

	} else {
		$passwordMessage = "Your old password is incorrect!<br><br>";
	}
} else {
	$passwordMessage = "";
}

if (isset($_POST['close_account'])) {
	header("Location: close_account.php");
}

?>