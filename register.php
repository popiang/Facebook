<?php  

session_start();

$conn = mysqli_connect("localhost", "test_user", "Abcd123!@#", "social");

if (mysqli_connect_errno()) {
	echo "Failed to connect: " . mysqli_connect_errno();
}

// declaring variables to prevent errors
$fname = "";		// first name
$lname = "";		// last name
$em = "";			// email
$em2 = "";			// email2
$password = "";		// password
$password2 = "";	// password2
$date = "";			// sign up date
$error_array = array();	// hold error messages

if (isset($_POST['register_button'])){

	// registeration form values
	$fname = ucfirst(strtolower(trim(strip_tags($_POST['reg_fname']))));
	$_SESSION['reg_fname'] = $fname;

	$lname = ucfirst(strtolower(trim(strip_tags($_POST['reg_lname']))));
	$_SESSION['reg_lname'] = $lname;

	$em = strtolower(trim(strip_tags($_POST['reg_email'])));
	$_SESSION['reg_email'] = $em;

	$em2 = strtolower(trim(strip_tags($_POST['reg_email2'])));
	$_SESSION['reg_email2'] = $em2;

	$password = strip_tags($_POST['reg_password']);
	$password2 = strip_tags($_POST['reg_password2']);
	$date = date("Y-m-d");

	// check if emails matched
	if ($em == $em2) {

		// check if email is in valid format
		if (filter_var($em, FILTER_VALIDATE_EMAIL)) {

			$em = filter_var($em, FILTER_VALIDATE_EMAIL);

			// check if email is already exist
			$e_check = mysqli_query($conn, "SELECT email FROM users WHERE email = '$em'");

			// count the number of rows returned
			$num_rows = mysqli_num_rows($e_check);

			if ($num_rows > 0) {
				array_push($error_array, "Email already in use!<br>");
			}

		} else {
			array_push($error_array, "Invalid email format!<br>");
		}

	} else {
		array_push($error_array, "Emails don't match!<br>");
	}

	if (strlen($fname) > 25 || strlen($fname) < 2) {
		array_push($error_array, "Your first name is must be between 2 and 25 characters!<br>");
	}

	if (strlen($lname) > 25 || strlen($lname) < 2) {
		array_push($error_array, "Your last name is must be between 2 and 25 characters!<br>");
	}	

	if($password != $password2) {
		array_push($error_array, "Your passwords do not match!<br>");
	} else {
		if (preg_match('/[^A-Za-z0-9]/', $password)) {
			array_push($error_array, "Your password can only contain english characters or numbers!<br>");
		}
	}

	if (strlen($password) > 30 || strlen($password) < 5) {
		array_push($error_array, "Your password must be between 5 and 30 characters!<br>");
	}
}

?>


<!DOCTYPE html>
<html>
<head>
	<title>Welcome to Facebook</title>
</head>
<body>

	<form action="register.php" method="POST">
		<input type="text" name="reg_fname" placeholder="First Name" 
			value="<?php echo isset($_SESSION['reg_fname']) ? $_SESSION['reg_fname'] : '' ?>" required>
		<br>
		<?php if(in_array("Your first name is must be between 2 and 25 characters!<br>", $error_array)) 
			echo "Your first name is must be between 2 and 25 characters!<br>"; ?>

		<input type="text" name="reg_lname" placeholder="Last Name" value="<?php echo isset($_SESSION['reg_lname']) ? $_SESSION['reg_lname'] : '' ?>" required>
		<br>
		<?php if(in_array("Your last name is must be between 2 and 25 characters!<br>", $error_array)) 
			echo "Your last name is must be between 2 and 25 characters!<br>"; ?>

		<input type="email" name="reg_email" placeholder="Email" value="<?php echo isset($_SESSION['reg_email']) ? $_SESSION['reg_email'] : '' ?>" required>
		<br>

		<input type="email" name="reg_email2" placeholder="Confirm Email" value="<?php echo isset($_SESSION['reg_email2']) ? $_SESSION['reg_email2'] : '' ?>" required>
		<br>
		<?php if(in_array("Emails don't match!<br>", $error_array)) echo "Emails don't match!<br>"; ?>
		<?php if(in_array("Invalid email format!<br>", $error_array)) echo "Invalid email format!<br>"; ?>
		<?php if(in_array("Email already in use!<br>", $error_array)) echo "Email already in use!<br>"; ?>


		<input type="password" name="reg_password" placeholder="Password" required>
		<br>
		<input type="password" name="reg_password2" placeholder="Confirm Password" required>
		<br>

		<?php if(in_array("Your passwords do not match!<br>", $error_array)) echo "Email already in use!<br>"; ?>

		
		<input type="submit" name="register_button" value="Register">
	</form>

</body>
</html>