<?php 
include "includes/header.php"; 

$messageObj = new Message($conn, $userLoggedIn);

if (isset($_GET['profile_username'])) {
	$username = $_GET['profile_username'];
	$userDetailsQuery = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
	$userArray = mysqli_fetch_array($userDetailsQuery);
	$numFriends = substr_count($userArray['friend_array'], ",") - 1;
}

if (isset($_POST['remove_friend'])) {
	$user = new User($conn, $userLoggedIn);
	$user->removeFriend($username);
}

if (isset($_POST['add_friend'])) {
	$user = new User($conn, $userLoggedIn);
	$user->sendRequest($username);
}

if (isset($_POST['respond_request'])) {
	header("Location: requests.php");
}

if (isset($_POST['post_message'])) {
	if (isset($_POST['message_body'])) {
		$body = mysqli_real_escape_string($conn, $_POST['message_body']);
		$date = date("Y-m-d H:i:s");
		$messageObj->sendMessage($username, $body, $date);
	}
	$link = '#profile_tabs a[href="#messages_div"]';
	echo "<script>
			$(function() {
				$('" . $link . "').tab('show'); 
			});
		  </script>";
}

?>

	<style type="text/css">
		.wrapper {
			margin-left: 0;
			padding-left: 0;
		}
	</style>

	<div class="profile_left">

		<!-- display profile picture -->
		<img src="<?php echo $userArray['profile_pic']; ?>">

		<!-- display basic info -->
		<div class="profile_info">
			<p><?php echo "Posts: " . $userArray['num_posts'];?></p>
			<p><?php echo "Likes: " . $userArray['num_likes']; ?></p> 
			<p><?php echo "Friends: " . $numFriends; ?></p> 
		</div>

		<!-- display button based on the relationship status between current user with the profile page user -->
		<form action="<?php echo $username; ?>" method="POST">

			<?php  
			$profileUserObj = new User($conn, $username);

			// check if the account of the user of the profile page is already closed -> directed to user_closed.php page
			if ($profileUserObj->isClosed()) {
				header("Location: user_closed.php");
			}

			$loggedInUserObj = new User($conn, $userLoggedIn);

			// check if the profile doesn't belong to the user opening the page
			// if it is, then no button will be displayed
			if ($userLoggedIn != $username) {

				// current viewing user is already friend with the profile page user -> display remove friend button
				if ($loggedInUserObj->isFriend($username)) {
					echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';

				// current viewing user have received friend request from the profile page user -> display response to request button
				} else if ($loggedInUserObj->didReceiveRequest($username)) {
					echo '<input type="submit" name="respond_request" class="warning" value="Response to Request"><br>';

				// current viewing user have sent friend request to the profile page user -> display message request sent					
				} else if ($loggedInUserObj->didSendRequest($username)) {
					echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
				
				// current viewing user is not friend and haven't sent of recieve friend request -> display add friend request button
				} else {
					echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
				}
			}

			?>

		</form>

		<!-- button to display the modal to submit post -->
		<input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">

		<?php  
		// display the number of mutual friends
		if ($userLoggedIn != $username) {
			echo "<div class='profile_info_bottom' >";
			echo $loggedInUserObj->getMutualFriends($username) . " Mutual Friends";
			echo "</div>";
		}
		?>

	</div>

	<div class="profile_main_column column">

		<ul class="nav nav-tabs" role="tablist" id="profile_tabs">
			<li class="nav-item">
				<a class="nav-link active" href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#about_div" aria-controls="about_div" role="tab" data-toggle="tab">About</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a>
			</li>
		</ul>

		<div class="tab-content">

			<div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
				<!-- this div will contain all the posts -->
				<div class="post_area"></div>
				<img id="loading" src="assets/images/icons/loading.gif">
			</div>

			<div role="tabpanel" class="tab-pane fade" id="about_div">
				
			</div>

			<div role="tabpanel" class="tab-pane fade" id="messages_div">

				<?php
					echo "<h4>You and <a href='" . $username . "'>" . $profileUserObj->getFirstAndLastName() . "</a></h4><hr><br>";
					echo "<div class='loaded_messages' id='scroll_messages'>";
					echo $messageObj->getMessages($username);
					echo "</div>";
				?>

				<div class="message_post">
					<!-- form to submit new message -->
					<form action="" method="POST">
						<textarea name='message_body' id='message_textarea' placeholder='Write your message...'></textarea>
						<input type='submit' name='post_message' class='info' id='message_submit' value='Send' >
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

		</div>
		
	</div>


	<!-- Modal to submit post -->
	<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">

				<div class="modal-header">
					<h5 class="modal-title" id="postModalLabel">Post something!</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<a href="">This will appear on the user's profile page and also their newsfeed for your friends to see!</a>

					<form action="" class="profile_post" method="POST">
						<div class="form-group">
							<textarea name="post_body" id="" cols="30" rows="" class="form-control"></textarea>
							<input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
							<input type="hidden" name="user_to" value="<?php echo $username; ?>">
						</div>
					</form>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
				</div>
			</div>
		</div>
	</div>	

	<script>
		
		// handling infinite scrolling	
		$(function(){

			var userLoggedIn = '<?php echo $userLoggedIn; ?>';
			var profileUsername = '<?php echo $username; ?>';
			var inProgress = false;

			//Load first posts
			loadPosts(); 

			$(window).scroll(function() {
				var bottomElement = $(".status_post").last();
				var noMorePosts = $('.post_area').find('.noMorePosts').val();
				if (isElementInView(bottomElement[0]) && noMorePosts === 'false') {
					loadPosts();
				}
			});
				
			function loadPosts() {

				// if it is already in the process of loading some posts, just return
				if(inProgress) { 
					return;
				}
			
				inProgress = true;
				$('#loading').show();

				// if nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'
				var page = $('.post_area').find('.nextPage').val() || 1; 
				
				$.ajax({

					url: "includes/handlers/ajax_load_profile_posts.php",
					type: "POST",
					data: "page=" + page + "&userLoggedIn=" + userLoggedIn + '&profileUsername=' + profileUsername,
					cache:false,
					success: function(response) {
						$('.post_area').find('.nextPage').remove(); //Removes current .nextpage
						$('.post_area').find('.noMorePosts').remove(); 
						$('.post_area').find('.noMorePostsText').remove();
						$('#loading').hide();
						$(".post_area").append(response);                                     
						inProgress = false;
					}
				});
			}
				
			// check if the element is in view
			function isElementInView (el) {

				if(el == null) {
					return;
				}

				var rect = el.getBoundingClientRect();

				return (
					rect.top >= 0 &&
					rect.left >= 0 &&
					rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && 
					rect.right <= (window.innerWidth || document.documentElement.clientWidth) 
				);
			}
		});		

	</script>

</div>

</body>
</html>