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
}

?>