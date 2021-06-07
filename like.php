<?php  

require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");

// check for logged in user
if (isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username'];
	$user_details_query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query);
} else {
	header("Location: register.php");
}

// get id of post
if (isset($_GET['post_id'])) {
	$postId = $_GET['post_id'];
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<link rel="stylesheet" href="assets/css/style.css">
	<style>
		body {
			background-color: #fff;
			font-family: Arial, Helvetica, Sans-sarif;
		}
	</style>
</head>
<body>

	<?php  
	$getLikes = mysqli_query($conn, "SELECT likes, added_by FROM posts WHERE id = '$postId'");
	$row = mysqli_fetch_array($getLikes);

	// total current likes for the post
	$totalLikes = $row['likes'];

	// user of the viewed post
	$userLiked = $row['added_by'];

	// get the current total likes received by the user/owner of the post
	$userDetailsQuery = mysqli_query($conn, "SELECT * FROM users WHERE username = '$userLiked'");
	$row = mysqli_fetch_array($userDetailsQuery);
	$totalUserLikes = $row['num_likes'];

	// like button
	if (isset($_POST['like_button'])) {

		// update the total likes for the post
		$totalLikes++;
		$query = mysqli_query($conn, "UPDATE posts SET likes = '$totalLikes' WHERE id = '$postId'");
	
		// update the total likes of the post owner
		$totalUserLikes++;
		$userLikes = mysqli_query($conn, "UPDATE users SET num_likes = '$totalUserLikes' WHERE username = '$userLiked'");

		// insert likes data into likes table
		$insertLikes = mysqli_query($conn, "INSERT INTO likes VALUES('', '$userLoggedIn', '$postId')");

		// insert notification
	}

	// unlike button
	if (isset($_POST['unlike_button'])) {

		// update the total likes for the post
		$totalLikes--;
		$query = mysqli_query($conn, "UPDATE posts SET likes = '$totalLikes' WHERE id = '$postId'");

		// update the total likes of the post owner
		$totalUserLikes--;
		$userLikes = mysqli_query($conn, "UPDATE users SET num_likes = '$totalUserLikes' WHERE username = '$userLiked'");

		// delete likes data from likes table
		$deleteLikes = mysqli_query($conn, "DELETE FROM likes WHERE username = '$userLoggedIn' AND post_id = '$postId'");
	}



	// check if logged in user already liked the post
	$checkQuery = mysqli_query($conn, "SELECT * FROM likes WHERE username = '$userLoggedIn' AND post_id = '$postId'");
	$numRows = mysqli_num_rows($checkQuery);

	if ($numRows > 0) {
		// already liked, so display unlike button
		echo '<form action="like.php?post_id=' . $postId . '" method="POST" class="like_unlike_button">
				<input type="submit" class="comment_like" name="unlike_button" value="Unlike" >
				<div class="like_value">
					' . $totalLikes . ' Likes
				</div>
			  </form>';
	} else {
		// haven't liked yet, so display like button
		echo '<form action="like.php?post_id=' . $postId . '" method="POST" class="like_unlike_button">
				<input type="submit" class="comment_like" name="like_button" value="Like" >
				<div class="like_value">
					' . $totalLikes . ' Likes
				</div>
			  </form>';
	}


	?>

</body>
</html>