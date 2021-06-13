<?php  

class Notification {

    private $user_obj;
    private $con;

	// good old constructor
    public function __construct($con, $user) {
        $this->con = $con;
        $this->user_obj = new User($this->con, $user);
    }

	public function getUnreadNumber() {

		$userLoggedIn = $this->user_obj->getUsername();

		$query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed='no' AND user_to='$userLoggedIn'");

		return mysqli_num_rows($query);
	}

	public function getNotifications($data, $limit) {

		// initialize variables
		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$returnString = "";

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		/////////////////////////////////////////////////////////////////////////////////////////////////////
		// when the logged in user opens the dropdown menu for notifications, technically it means all the //
		// notifications of the logged in user in the dropdown menu are considered viewed     			   //
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		$setViewedQuery = mysqli_query($this->con, "UPDATE notifications SET viewed = 'yes' WHERE user_to = '$userLoggedIn'");

		// get all the notifications for the logged in user
		$query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to = '$userLoggedIn' ORDER BY id DESC");

		if (mysqli_num_rows($query) == 0) {
			echo "You have no notifications!";
			return;
		}

		// number of messages checked
		$numIterations = 0;

		// number of messages posted
		$count = 1;

		// looping through all the other users and get the latest message and then construct the structure to display
		while($row = mysqli_fetch_array($query)) {

			// skip all the messages from the first/previous pages which are alruserDataQady displayed
			if ($numIterations++ < $start) {
				continue;
			}

			//////////////////////////////////////////////////////////////////////////////////
			// 2 ways to exit the foreach looping                                        	//
			//   1. the number of notifications appended to variable returnString == limit  //
			//   2. no more notification data to be appended                                //
			//////////////////////////////////////////////////////////////////////////////////
			if ($count++ > $limit) {
				break;
			}

			// get the data of the user who triggered the notification
			$userFrom = $row['user_from'];
			$userDataQuery = mysqli_query($this->con, "SELECT * FROM users WHERE username = '$userFrom'");
			$userData = mysqli_fetch_array($userDataQuery);

			// timeframe message
			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($row['datetime']); // time of post
			$end_date = new DateTime($date_time_now); // current time
			$interval = $start_date->diff($end_date); // difference between dates

			if ($interval->y >= 1) {

				if ($interval == 1) {
					$time_message = $interval->y . " year ago";
				} else {
					$time_message = $interval->y . " years ago";
				}

			} else if ($interval->m >= 1) {

				if ($interval->d == 0) {
					$days = " ago";
				} else if ($interval->d == 1) {
					$days = $interval->d . " day ago";
				} else {
					$days = $interval->d . " days ago";
				}

				if ($interval->m == 1) {
					$time_message = $interval->m . " month" . $days;
				} else {
					$time_message = $interval->m . " months" . $days;
				}

			} else if ($interval->d >= 1) {

				if ($interval->d == 1) {
					$time_message = "Yesterday";
				} else {
					$time_message = $interval->d . " days ago";
				}

			} else if ($interval->h >= 1) {

				if ($interval->h == 1) {
					$time_message = $interval->h . " hour ago";
				} else {
					$time_message = $interval->h . " hours ago";
				}

			} else if ($interval->i >= 1) {

				if ($interval->i == 1) {
					$time_message = $interval->i . " minute ago";
				} else {
					$time_message = $interval->i . " minutes ago";
				}

			} else {

				if ($interval->s > 30) {
					$time_message = "Just now";
				} else {
					$time_message = $interval->s . " seconds ago";
				}
			}

			// these 3 lines are to set the color of the unopened messages in the dropdown
			$opened = $row['opened'];
			$style = (isset($row['opened']) && $row['opened'] == 'no') ? "background-color: #DDEDFF" : "";

			// structure to display the conversation of the logged in user
			$returnString .= "<a href='" . $row['link'] . "'>
								<div class='result_display result_display_notification' style='". $style ."'>
									<div class='notificationsProfilePic'>
										<img src='" . $userData['profile_pic'] . "'>
									</div>
									<p class='timestamp_smaller' id='grey'>" . $time_message . "</p>" . $row['message'] . "
								</div>
							  </a>";
		}

		// return additional info to the calling page
		if ($count > $limit) {
			// data returned indicates there's more data to be displayed
			$returnString .= "<input type='hidden' class='nextPageDropDownData' value='" . ($page + 1) . "'>
									<input type='hidden' class='noMoreDropdownData' value='false'>";
		} else {
			// data returned indicates there's no more data to be displayed
			$returnString .= "<input type='hidden' class='noMoreDropdownData' value='true'>
									<p style='text-aling:center;'>No more notifications to load</p>";
		}

		return $returnString;
	}

	public function insertNotification($postId, $userTo, $type) {

		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedInName = $this->user_obj->getFirstAndLastName();

		$dateTime = date("Y-m-d H:i:s");

		switch ($type) {

			case 'comment':
				$message = $userLoggedInName . " commented on your post";
				break;

			case 'like':
				$message = $userLoggedInName . " liked your post";
				break;

			case 'profile_post':
				$message = $userLoggedInName . " posted on your profile";
				break;

			case 'comment_non_owner':
				$message = $userLoggedInName . " commented on a post you commented on";
				break;

			case 'profile_comment':
				$message = $userLoggedInName . " commented on your profile post";
				break;
		}

		$link = "post.php?id=" .  $postId;

		$insertQuery = mysqli_query($this->con, "INSERT INTO notifications VALUES ('', '$userTo', '$userLoggedIn', '$message', '$link', '$dateTime', 'no', 'no')");
	}
}

?>