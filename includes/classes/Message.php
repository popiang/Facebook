<?php

class Message {

    private $user_obj;
    private $con;

	// good old constructor
    public function __construct($con, $user) {
        $this->con = $con;
        $this->user_obj = new User($this->con, $user);
    }

	// get the username of the other user of the most recent conversation with the logged in user
	public function getMostRecentUser() {
		$userLoggedIn = $this->user_obj->getUsername();

		// get the latest message of the logged in user if available, whether sending or receiving
		$query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to = '$userLoggedIn' OR user_from = '$userLoggedIn' ORDER BY id DESC LIMIT 1");

		// return false if none
		if (mysqli_num_rows($query) == 0) {
			return false;
		}

		$row = mysqli_fetch_array($query);

		// assign the value of the latest message accordingly
		$userTo = $row['user_to'];
		$userFrom = $row['user_from'];

		// return the user in the message who is not the logged in user
		if ($userTo != $userLoggedIn) {
			return $userTo;
		} else {
			return $userFrom;
		}
	}

	// simply insert the message of logged in user to userTo into table
	public function sendMessage($userTo, $body, $date) {
		if ($body != "") {
			$userLoggedIn = $this->user_obj->getUsername();
			$query = mysqli_query($this->con, "INSERT INTO messages VALUES ('', '$userTo', '$userLoggedIn', '$body', '$date', 'no', 'no', 'no')");
		}
	}

	// get all the conversation messages between the logged in user with another user
	public function getMessages($otherUser) {
		
		$userLoggedIn = $this->user_obj->getUsername();
		$data = "";

		// update message as opened/viewed by user
		$query = mysqli_query($this->con, "UPDATE messages SET opened = 'yes' WHERE user_to = '$userLoggedIn' AND user_from = '$otherUser'");

		// get conversation messages between logged in user and the other user
		$getMessagesQuery = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to = '$userLoggedIn' AND user_from = '$otherUser') OR (user_from = '$userLoggedIn' AND user_to = '$otherUser')");

		while ($row = mysqli_fetch_array($getMessagesQuery)) {
			$userTo = $row['user_to'];
			$userFrom = $row['user_from'];
			$body = $row['body'];

			// to set the logged in user message with div with id green and the other user with div with id blue
			$divTop = ($userTo == $userLoggedIn) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
			$data = $data . $divTop . $body . "</div><br><br>";
		}

		return $data;
	}

	// to get the latest message to display on the profile left column
	public function getLatestMessage($userLoggedIn, $userTo) {

		$detailsArray = array();

		// get the latest message
		$query = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to = '$userLoggedIn' AND user_from = '$userTo') OR (user_to = '$userTo' AND user_from = '$userLoggedIn') ORDER BY id DESC LIMIT 1");

		$row = mysqli_fetch_array($query);

		// to choose the opening words based on who sent the message
		$sentBy = $row['user_to'] == $userLoggedIn ? "They said: " : "You said: ";

		// timeframe message
		$date_time_now = date("Y-m-d H:i:s");
		$start_date = new DateTime($row['date']); // time of post
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

		// an array with 3 data of the latest message
		array_push($detailsArray, $sentBy);
		array_push($detailsArray, $row['body']);
		array_push($detailsArray, $time_message);

		return $detailsArray;
	}

	// get all the conversations of the logged in user (not the whole content of the conversations)
	public function getConversations() {

		// initialize variables
		$userLoggedIn = $this->user_obj->getUsername();
		$returnString = "";
		$otherUsers = array();

		// get all the conversations of the logged in user
		$query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to = '$userLoggedIn' OR user_from = '$userLoggedIn' ORDER BY id DESC");

		// this loop is to get all the other uses in all of the conversations and push it into an array
		while($row = mysqli_fetch_array($query)) {

			// get the other user
			$userToPush = $row['user_to'] != $userLoggedIn ? $row['user_to'] : $row['user_from'];

			// push into array if the other users are not already in
			if (!in_array($userToPush, $otherUsers)) {
				array_push($otherUsers, $userToPush);
			}
		}

		// looping through all the other users and get the latest message and then construct the structure to display
		foreach($otherUsers as $username) {

			$userFoundObj = new User($this->con, $username);

			// get the latest message
			$latestMessageDetails = $this->getLatestMessage($userLoggedIn, $username);

			// display only the first 12 characters of the message and then appended with '...'
			$dots = strlen($latestMessageDetails[1]) >= 12 ? "..." : "";
			$split = str_split($latestMessageDetails[1], 12);
			$split = $split[0] . $dots;

			// structure to display the conversation of the logged in user
			$returnString .= "<a href='messages.php?u=$username'> 
								  <div class='user_found_messages'>
								  	<img src='" . $userFoundObj->getProfilePic() . "' style='border-radius:5px; margin-right:5px;' >
								  	" . $userFoundObj->getFirstAndLastName() . "
								  	<span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[2] . "</span>
								  	<p id='grey' style='margin:0;'>" . $latestMessageDetails[0]. $split  . "</p>
								  </div>
							  </a>";
		}

		return $returnString;
	}

	// get all the latest conversation of the logged in user to be displayed in dropdown
	public function getConvosDropdown($data, $limit) {

		// initialize variables
		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$returnString = "";
		$otherUsers = array();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////
		// when the logged in user opens the dropdown menu for messages, technically it means all the //
		// messages/conversation of the logged in user in the dropdown menu are considered viewed     //
		////////////////////////////////////////////////////////////////////////////////////////////////
		$setViewedQuery = mysqli_query($this->con, "UPDATE messages SET viewed = 'yes' WHERE user_to = '$userLoggedIn'");

		// get all the user_to & user_from from all the messages of the logged in user
		$query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to = '$userLoggedIn' OR user_from = '$userLoggedIn' ORDER BY id DESC");

		// this loop is to get all the other users in all of the above messages and push it into an array
		while ($row = mysqli_fetch_array($query)) {

			// get the other user
			$userToPush = $row['user_to'] != $userLoggedIn ? $row['user_to'] : $row['user_from'];

			// push into array if the other users are not already in
			if (!in_array($userToPush, $otherUsers)) {
				array_push($otherUsers, $userToPush);
			}
		}

		// number of messages checked
		$numIterations = 0;

		// number of messages posted
		$count = 1;

		// looping through all the other users and get the latest message and then construct the structure to display
		foreach ($otherUsers as $username) {
  
  			// skip all the messages from the first/previous pages which are already displayed
			if ($numIterations++ < $start) {
				continue;
			}

			//////////////////////////////////////////////////////////////////////////////
			// 2 ways to exit the foreach looping										//
			//   1. the number of messages appended to variable returnString == limit 	//
			//   2. no more messages data to be appended								//
			//////////////////////////////////////////////////////////////////////////////
			if ($count++ > $limit) {
				break;
			}

			// these 3 lines are to set the color of the unopened messages in the dropdown
			$isUnreadQuery = mysqli_query($this->con, "SELECT opened FROM messages WHERE user_to = '$userLoggedIn' AND user_from = '$username' ORDER BY id DESC");
			$row = mysqli_fetch_array($isUnreadQuery);
			$style = (isset($row['opened']) && $row['opened'] == 'no') ? "background-color: #DDEDFF" : "";

			$userFoundObj = new User($this->con, $username);

			// get the latest message
			$latestMessageDetails = $this->getLatestMessage($userLoggedIn, $username);

			// display only the first 12 characters of the message and then appended with '...'
			$dots = strlen($latestMessageDetails[1]) >= 12 ? "..." : "";
			$split = str_split($latestMessageDetails[1], 12);
			$split = $split[0] . $dots;

			// structure to display the conversation of the logged in user
			$returnString .= "<a href='messages.php?u=$username'> 
								<div class='user_found_messages'  style='" . $style . "'>
									<img src='" . $userFoundObj->getProfilePic() . "' style='border-radius:5px; margin-right:5px;' >
									" . $userFoundObj->getFirstAndLastName() . "
									<span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[2] . "</span>
									<p id='grey' style='margin:0;'>" . $latestMessageDetails[0] . $split . "</p>
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
							  <p style='text-aling:center;'>No more messages to load</p>";
		}

		return $returnString;		
	}

	// get the number of unread messages of the logged in user
	public function getUnreadNumber() {

		$userLoggedIn = $this->user_obj->getUsername();

		$query = mysqli_query($this->con, "SELECT * FROM messages WHERE viewed='no' AND user_to='$userLoggedIn'");

		return mysqli_num_rows($query);
	}
}

?>