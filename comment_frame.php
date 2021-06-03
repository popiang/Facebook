<?php  

require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");

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
	<title></title>
	<link rel="stylesheet" href="assets/css/style.css">
	<style type="text/css">
		* {
			font-size: 12px;
			font-family: Arial, Helvetica, Sans-serif;
		}
	</style>
</head>
<body>

	<script>
		
	function toggle() {
		var element = document.getElementById('comment_section');

		if (element.style.display == 'block') {
			element.style.display = 'none';
		} else {
			element.style.display = 'block';
		}
	}

	</script>

	<?php  

	// get id of post
	if (isset($_GET['post_id'])) {
		$postId = $_GET['post_id'];
	}

	// get the username of user of the post which the comment will be added to
	// but actually it's not needed because post id is sufficient 
	$userQuery = mysqli_query($conn, "SELECT added_by, user_to FROM posts WHERE id='$postId'");
	$row = mysqli_fetch_array($userQuery);
	$postedTo = $row['added_by'];

	// insert the comment into table
	if (isset($_POST['postComment' . $postId])) {
		$postBody = $_POST['post_body'];
		$postBody = mysqli_escape_string($conn, $postBody);
		$dateTimeNow = date('Y-m-d H:i:s');
		$insertPost = mysqli_query($conn, "INSERT INTO comments VALUES ('', '$postBody', '$userLoggedIn', '$postedTo', '$dateTimeNow', 'no', '$postId')");
		echo "<p>Comment Posted!</p>";
	}

	?>

	<!-- comment form -->
	<form action="comment_frame.php?post_id=<?php echo $postId; ?>" method="POST" id="comment_form" name="postComment<?php echo $postId; ?>" >
		<textarea name="post_body"></textarea>
		<input type="submit" name="postComment<?php echo $postId; ?>" value="Post">
	</form>

	<!-- load comments -->
	<?php  

	$getComments = mysqli_query($conn, "SELECT * FROM comments WHERE post_id = '$postId' ORDER BY id ASC");
	$count = mysqli_num_rows($getComments);

	if ($count != 0) {

		while ($comment = mysqli_fetch_array($getComments)) {

			$commentBody = $comment['post_body'];
			$postedTo = $comment['posted_to'];
			$postedBy = $comment['posted_by'];
			$dateAdded = $comment['date_added'];
			$removed = $comment['removed'];

			// timeframe
			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($dateAdded);		// time of post
			$end_date = new DateTime($date_time_now);	// current time
			$interval = $start_date->diff($end_date);	// difference between dates
			
			if ($interval->y >= 1){

				if ($interval == 1){
					$time_message = $interval->y . " year ago";
				} else {
					$time_message = $interval->y . " years ago";
				}

			} else if ($interval->m >= 1) {

				if ($interval->d == 0) {
					$days = " ago";
				} else if ($interval->d == 1){
					$days = $interval->d . " day ago";
				} else {
					$days = $interval->d . " days ago";
				}

				if ($interval->m == 1){
					$time_message = $interval->m . " month". $days;
				} else {
					$time_message = $interval->m . " months". $days;
				}

			} else if ($interval->d >= 1) {

				if ($interval->d == 1){
					$time_message = "Yesterday";
				} else {
					$time_message = $interval->d . " days ago";
				}

			} else if ($interval->h >= 1) {

				if ($interval->h == 1){
					$time_message = $interval->h . " hour ago";
				} else {
					$time_message = $interval->h . " hours ago";
				}

			} else if ($interval->i >= 1) {

				if ($interval->i == 1){
					$time_message = $interval->i . " minute ago";
				} else {
					$time_message = $interval->i . " minutes ago";
				}

			} else {

				if ($interval->s > 30){
					$time_message = "Just now";
				} else {
					$time_message = $interval->s . " seconds ago";
				}
			}

			$userObj = new User($conn, $postedBy);

			?>

			<div class="comment_section">
				<a href="<?php echo $postedBy;?>" target="_parent"><img src="<?php echo $userObj->getProfilePic(); ?>" title="<?php echo $postedBy; ?>" style="float:left;" height="30"></a>
				<a href="<?php echo $postedBy;?>" target="_parent"><b> <?php echo $userObj->getFirstAndLastName(); ?> </b></a>
				&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $time_message . "<br>" . $commentBody; ?>
				<hr>
			</div>

			<?php
		}
	} else {
		echo "<center><br><br>No comments to show!</center>";
	}

	?>

</body>
</html>