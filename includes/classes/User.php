<?php  

class User {
	
	private $user;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$user_details_query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user'");
		$this->user = mysqli_fetch_array($user_details_query);
	}

	public function getUsername () {
		return $this->user['username'];
	}

	public function getNumPosts() {
		return $this->user['num_posts'];
	}

	public function getFirstAndLastName() {
		return $this->user['first_name'] . " " . $this->user['last_name'];
	}

	public function getProfilePic() {
		return $this->user['profile_pic'];
	}

	public function isClosed() {
		if ($this->user['user_closed'] == 'yes') {
			return true;
		} else {
			return false;
		}
	}

	public function isFriend($usernameToCheck) {

		$usernameComma = "," . $usernameToCheck . ",";

		if (strstr($this->user['friend_array'], $usernameComma) || $usernameToCheck == $this->user['username']) {
			return true;
		} else {
			return false;
		}
	}

	public function didReceiveRequest($userFrom) {
		$userTo = $this->user['username'];
		$checkRequestQuery = mysqli_query($this->con, "SELECT * FROM friend_request WHERE user_to='$userTo' AND user_from='$userFrom'");

		if (mysqli_num_rows($checkRequestQuery) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function didSendRequest($userTo) {
		$userFrom = $this->user['username'];
		$checkRequestQuery = mysqli_query($this->con, "SELECT * FROM friend_request WHERE user_to='$userTo' AND user_from='$userFrom'");

		if (mysqli_num_rows($checkRequestQuery) > 0) {
			return true;
		} else {
			return false;
		}
	}	

	public function removeFriend($userToRemove){
		$loggedInUser = $this->user['username'];

		$query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username = '$userToRemove'");
		$row = mysqli_fetch_array($query);
		$currentFriendArray = $row['friend_array'];

		// remove $userToRemove from $loggedInUser friend list in table
		$newFriendArray = str_replace($userToRemove . ",", "", $this->user['friend_array']);
		$removeFriendQuery = mysqli_query($this->con, "UPDATE users SET friend_array = '$newFriendArray' WHERE username = '$loggedInUser'");

		// remove $loggedInUser from $userToRemove friend list in table
		$newFriendArray = str_replace($loggedInUser . ",", "", $currentFriendArray);
		$removeFriendQuery = mysqli_query($this->con, "UPDATE users SET friend_array = '$newFriendArray' WHERE username = '$userToRemove'");
	}

	public function sendRequest($userTo) {
		$userFrom = $this->user['username'];
		$query = mysqli_query($this->con, "INSERT INTO friend_request VALUES ('', '$userTo', '$userFrom')");
	}

	public function getFriendArray() {
		return $this->user['friend_array'];
	}

	public function getMutualFriends($userToCheck) {
		$mutualFriends = 0;

		$friendsArray = $this->user['friend_array'];
		$friends = explode(",", $friendsArray);

		$query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username = '$userToCheck'");
		$row = mysqli_fetch_array($query);
		$userToCheckFriendArray = $row['friend_array'];
		$userToCheckFriends = explode(",", $userToCheckFriendArray);

		foreach($friends as $i) {
			foreach($userToCheckFriends as $j) {
				if ($i == $j && $i != "") {
					$mutualFriends++;
				}
			}
		}

		return $mutualFriends;
	}
}

?>