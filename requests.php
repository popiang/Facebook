<?php  
include "includes/header.php";
?>

<div class="main_column column" id="main_column">

	<h4>Friend Request</h4>

	<?php 
	
	// get all the friend requests received by the logged in user
	$query = mysqli_query($conn, "SELECT * FROM friend_request WHERE user_to = '$userLoggedIn'");

	if (mysqli_num_rows($query) == 0) {
		echo "You have no friend request at the moment";
	} else {

		// looping through all the friend request
		while ($row = mysqli_fetch_array($query)) {

			$userFrom = $row['user_from'];
			$userFromObj = new User($conn, $userFrom);

			echo $userFromObj->getFirstAndLastName() . " sent you a friend request!";

			$userFromFriendArray = $userFromObj->getFriendArray();

			// if accept friend request button is pressed
			if (isset($_POST['accept_request' . $userFrom])) {

				// update friends array list for both users
				$addFriendQuery = mysqli_query($conn, "UPDATE users SET friend_array=CONCAT(friend_array, '$userFrom,') WHERE username = '$userLoggedIn'");
				$addFriendQuery = mysqli_query($conn, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username = '$userFrom'");

				// remove friend request entry from table
				$deleteQuery = mysqli_query($conn, "DELETE FROM friend_request WHERE user_to = '$userLoggedIn' AND user_from = '$userFrom'");

				echo "You are now friends!!";
				header("Location: requests.php");
			}

			// if ignore friend request button is pressed
			if (isset($_POST['ignore_request' . $userFrom])) {

				// remove friend request entry from table
				$deleteQuery = mysqli_query($conn, "DELETE FROM friend_request WHERE user_to = '$userLoggedIn' AND user_from = '$userFrom'");

				echo "Request ignored";
				header("Location: requests.php");
			}

			?>
			<!-- buttons to accept or ignore friend requests -->
			<form action="requests.php" method="POST">
				<input type="submit" name="accept_request<?php echo $userFrom; ?>" id="accept_button" class="success" value="Accept">
				<input type="submit" name="ignore_request<?php echo $userFrom; ?>" id="ignore_button" class="danger" value="Ignore">
			</form>
			<?php

		}
	}
	
	?>

</div>