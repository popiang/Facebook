<?php 

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

	// check first name length
	if (strlen($fname) > 25 || strlen($fname) < 2) {
		array_push($error_array, "Your first name is must be between 2 and 25 characters!<br>");
	}

	// check last name length
	if (strlen($lname) > 25 || strlen($lname) < 2) {
		array_push($error_array, "Your last name is must be between 2 and 25 characters!<br>");
	}	

	// check password match
	if($password != $password2) {
		array_push($error_array, "Your passwords do not match!<br>");
	} else {
		// check if password non alphanumeric characters
		if (preg_match('/[^A-Za-z0-9]/', $password)) {
			array_push($error_array, "Your password can only contain english characters or numbers!<br>");
		}
	}

	// check password length
	if (strlen($password) > 30 || strlen($password) < 5) {
		array_push($error_array, "Your password must be between 5 and 30 characters!<br>");
	}

	if (empty($error_array)) {
		
		// encrypt the password
		$password = md5($password);	

		// generate username by concatenating first name and last name
		$username = strtolower($fname . "_" . $lname);

		$check_username_query = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");

		$i = 0;
		// if username exist, add number to username
		while(mysqli_num_rows($check_username_query) != 0) {
			$i++;
			$username = $username . "_" + $i;
			$check_username_query = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
		}

		// profile picture assignment
		$rand = rand(1,2);

		if($rand == 1)
			$profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
		else
			$profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";

		// insert finalized registration input into users table
		$query = mysqli_query($conn, "INSERT INTO users VALUES ('', '$fname', '$lname', '$username', '$em', '$password', '$date', '$profile_pic', '0', '0', 'no', ',')");

		array_push($error_array, "<span style='color:#14C800'>You're all set! Go ahead and login!</span>");

		// clear session variables
		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";
	}
}

?>