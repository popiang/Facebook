<?php  
include("../../config/config.php");
include("../classes/User.php");

// receive the data from the javascript function
$input = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $input);

if (strpos($input, "_") !== false) {

	// assume the input is username coz got underscore
	$usersReturned = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '$input%' AND user_closed='no' LIMIT 8");

} else if (count($names) == 2) {

	// assume the inputs are the first and last name coz array length is 2
	$usersReturned = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed='no' LIMIT 8");

} else {

	// array length is 1 and without underscore, should be 1 word of first name or last name
	$usersReturned = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND user_closed='no' LIMIT 8");

}

if ($input != "") {

	// loop to construct the display struction with all the data
	while($row = mysqli_fetch_array($usersReturned)) {

		$user = new User($conn, $userLoggedIn);

		if ($row['username'] != $userLoggedIn) {

			// get mutual friends count if result is other user then the logged in user
			$mutualFriends = $user->getMutualFriends($row['username']) . " friends in common";

		} else {

			// result is the same with logged in user, so not displaying mutual friends count
			$mutualFriends = "";

		}

		// the structure to display
		if ($user->isFriend($row['username'])) {
			echo "<div class='result_display'>
					<a href='messages.php?u='" . $row['username'] . "' style='color:#000;'>
						<div class='live_search_profile_pic'>
							<img src='" . $row['profile_pic'] . "'>
						</div>
						<div class='live_search_text'>
							" . $row['first_name'] . " " . $row['last_name'] . "
							<p style='margin:0'>" . $row['username'] . "</p>
							<p id='grey'>" . $mutualFriends . "</p>
						</div>
					</a>
				  </div>";

		}
	}
}

?>