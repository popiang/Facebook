<?php  
require 'config/config.php';

if (isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username'];
	$user_details_query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query);
} else {
	header("Location: register.php");
}

?>

<!DOCTYPE html>
<html>

<head>

	<title>Facebook</title>

	<!-- CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/css/style.css">

	<!-- JAVASRIPT -->
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
	<script src="https://kit.fontawesome.com/c814600906.js" crossorigin="anonymous"></script>

</head>

<body>

	<div class="top_bar">

		<div class="logo">
			<a href="index.php">Facebook</a>
		</div>
		
		<nav>
			<a href=""><?php echo $user['first_name']; ?></a>
			<a href="#" class="ml-3"><li class="fas fa-home fa-lg"></li></a>
			<a href="#" class="ml-3"><li class="fas fa-envelope fa-lg"></li></a>
			<a href="#" class="ml-3"><li class="fas fa-bell fa-lg"></li></a>
			<a href="#" class="ml-3"><li class="fas fa-users fa-lg"></li></a>
			<a href="#" class="ml-3 mr-2"><li class="fas fa-cog fa-lg"></li></a>
		</nav>

	</div>

