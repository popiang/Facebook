<?php  
include ("includes/header.php");

$messageObj = new Message($conn, $userLoggedIn);

if (isset($_GET['u'])) {

	// if there's a user profile name in the url, set it to $userTo
	$userTo = $_GET['u'];

} else {
	
	// if there's no profile user name in the url, simply get the user of the latest conversation
	$userTo = $messageObj->getMostRecentUser(); 

	// but if there's no recent message at all, no recent user, so userTo will be set to 'new'
	if ($userTo == false) {
		$userTo = 'new';
	}
}

// for existing converstion, create user obj of the other user
if ($userTo != 'new') {
	$userToObj = new User($conn, $userTo);
}

// when submit message button is pressed
if (isset($_POST['post_message'])) {
	if (isset($_POST['message_body'])) {
		$body = mysqli_real_escape_string($conn, $_POST['message_body']);
		$date = date("Y-m-d H:i:s");
		$messageObj->sendMessage($userTo, $body, $date);
	}
}

?>

<!-- left top column to display basic info of the logged in user -->
<div class="user_details column">		
	<a href="<?php echo $userLoggedIn; ?>"><img src="<?php echo $user['profile_pic'];?>" alt=""></a>
	<div class="user_details_left_right">
		<a href="<?php echo $userLoggedIn; ?>">
			<?php  
			echo $user['first_name'] . " " . $user['last_name'];
			?>
		</a>
		<?php  
			echo "Posts: " . $user['num_posts'] . "<br>";
			echo "Likes: " . $user['num_likes'];
		?>
	</div>
</div>

<!-- the main conversation messages column -->
<div class="main_column column" id="main_column">

	<?php  
	if ($userTo != 'new') {

		// for existing conversation, list down all the messages between logged in user with $userTo
		echo "<h4>You and <a href='$userTo'>" . $userToObj->getFirstAndLastName() . "</a></h4><hr><br>";
		echo "<div class='loaded_messages' id='scroll_messages'>";
		echo $messageObj->getMessages($userTo);
		echo "</div>";

	} else {

		// simply to display the header of the section to search for a friend to start a conversation
		echo "<h4>New Message</h4>";

	}
	?>

	<div class="message_post">
		<!-- the message form -->
		<form action="" method="POST">
		
			<?php  
			if ($userTo == "new") {

				// to create a conversation with a friend who the logged in user doesn't have a conversation yet
				echo "Select the friend you would like to message <br><br>";
				?>

				<!-- the search will be done by javascript function getUser and the result will be appended to below .result div -->
				To: <input type='text' onkeyup='getUser(this.value, "<?php echo $userLoggedIn; ?>")' name='q' placeholder='Name' autocomplete='off' id='search_text_input'>

				<?php
				// result of the search will be placed in below div
				echo "<div class='results'></div>";

			} else {
				// submit message to existing conversation
				echo "<textarea name='message_body' id='message_textarea' placeholder='Write your message...'></textarea>";
				echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send' >";
			}
			?>

		</form>
	</div>

	<script>
		// this will load the screen to the bottom of the page to display the latest submitted message
		let div = document.getElementById('scroll_messages');
		if (div != null) {
			div.scrollTop = div.scrollHeight;
		}
	</script>

</div>

<!-- list down all the conversation the logged in user has -->
<div class="user_details column" id="conversations">
	<h4>Conversations</h4>
	<div class="loaded_conversations">
		<?php echo $messageObj->getConversations(); ?>
	</div>
	<br>
	<a href="messages.php?u=new">New Message</a>
</div>