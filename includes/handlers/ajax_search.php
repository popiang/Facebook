<?php  
include "../../config/config.php";
include "../../includes/classes/User.php";

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

// if query contains an underscore, assume user is search for username
if (strpos($query, '_') !== false) {
	$usersReturnedQuery = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
} else if (count($names) == 2) {
	$usersReturnedQuery = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no' LIMIT 8");
} else {
	$usersReturnedQuery = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no' LIMIT 8");
}

if ($query != "") {
	
	while($row = mysqli_fetch_array($usersReturnedQuery)) {

		$user = new User($conn, $userLoggedIn);

		if ($row['username'] != $userLoggedIn) {
			$mutualFriends = $user->getMutualFriends($row['username']) . " friends in common";
		} else {
			$mutualFriends = "";
		}

		echo "<div class='result_display result_display_search'>
				<a href='" . $row['username'] . "' style='color:#1485BD'>
					<div class='live_search_profile_pic'>
						<img src='" . $row['profile_pic'] . "'>
					</div>
					<div class='live_search_text main_live_search_text'>
						" . $row['first_name'] . " " . $row['last_name'] . "
						<p>" . $row['username'] . "</p>
						<p id='grey'>" . $mutualFriends . "</p>
					</div>
				</a>
			  </div>";
	}
}

?>