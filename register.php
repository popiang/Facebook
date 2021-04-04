<?php  

require "config/config.php";
require "includes/form_handlers/register_handler.php";
require "includes/form_handlers/login_handler.php";

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Welcome to Facebook</title>
		<link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="assets/js/register.js"></script>
	</head>
	<body>

		<?php 

		// if (isset($_POST['register_button'])){
		// 	echo '
		// 	<script>
		// 		$(document).ready(function(){
		// 			$("#first").hide();
		// 			$("#second").show();
		// 		});
		// 	</script>
		// 	';
		// }

		?>

		<div class="wrapper">

			<div class="login_box">

				<div class="login_header">
					<h1>Facebook</h1>
					Login or sign up below!
				</div>

				<br>
				
				<div id="first">

					<form action="register.php" method="POST">
						<input type="email" name="log_email" placeholder="Email Address" value="<?php echo isset($_SESSION['log_email']) ? $_SESSION['log_email'] : '' ?>" required>
						<br>
						<input type="password" name="log_password" placeholder="Password">
						<br>
						<input type="submit" name="login_button" value="Login">
						<?php if(in_array("Email or password is incorrect!<br>", $error_array)) 
							echo "Email or password is incorrect!<br>"; ?>
						<br>
						<a href="#" id="signup" class="signup">Need an account? Register here!</a>
					</form>

				</div>

				<div id="second">

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
						
						<?php 
						if(in_array("Emails don't match!<br>", $error_array)) 
							echo "Emails don't match!<br>"; 
						else if(in_array("Invalid email format!<br>", $error_array)) 
							echo "Invalid email format!<br>"; 
						else if(in_array("Email already in use!<br>", $error_array)) 
							echo "Email already in use!<br>"; 
						?>

						<input type="password" name="reg_password" placeholder="Password" required>
						<br>
						<input type="password" name="reg_password2" placeholder="Confirm Password" required>
						<br>

						<?php 
						if(in_array("Your passwords do not match!<br>", $error_array)) 
							echo "Your passwords do not match!<br>";
						else if(in_array("Your password can only contain english characters or numbers!<br>", $error_array)) 
							echo "Your password can only contain english characters or numbers!<br>";
						else if(in_array("Your password must be between 5 and 30 characters!<br>", $error_array)) 
							echo "Your password must be between 5 and 30 characters!<br>"; 
						?>		

						<input type="submit" name="register_button" value="Register" id="register_button">
						<br>

						<?php if(in_array("<span style='color:#14C800'>You're all set! Go ahead and login!</span>", $error_array)) 
							echo "<span style='color:#14C800'>You're all set! Go ahead and login!</span>"; ?>

						<a href="#" id="signin" class="signin">Already have an account? Sign in here!</a>	

					</form>

				</div>

			</div>

		</div>

	</body>

</html>