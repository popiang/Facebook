<?php  

class Post {
	
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($this->con, $user);
	}

	public function submitPost($body, $user_to, $imageName) {
		
		$body = strip_tags($body); //remove html tags
		$body = mysqli_real_escape_string($this->con, $body);

		$body = str_replace('\r\n', '\n', $body);
		$body = nl2br($body);

		$body = trim($body);
		
		if ($body != "") {

			$bodyArray = preg_split("/\s+/", $body);

			foreach($bodyArray as $key => $value) {
				if (strpos($value, "www.youtube.com/watch?v=") !== false) {

					$link = preg_split("!&!", $value);

					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
					$value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value ."\'><iframe><br>";
					$bodyArray[$key] = $value;
				}
			}

			$body = implode(" ", $bodyArray);

			// current date and time
			$date_added = date("Y-m-d H:i:s");

			// get username
			$added_by = $this->user_obj->getUsername();

			// if user is on own profile, user_to is 'none'
			if ($user_to == $added_by) {
				$user_to = "none";
			}

			// insert post
			$query = mysqli_query($this->con, "INSERT INTO posts VALUES ('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$imageName')");

			$returned_id = mysqli_insert_id($this->con);

			// insert notification
			// only insert notification when submit post to other user
			if ($user_to != 'none') {
				$notification = new Notification($this->con, $added_by);
				$notification->insertNotification($returned_id, $user_to,"profile_post");
			}

			// update post count for user
			$num_posts = $this->user_obj->getNumPosts();
			$num_posts++;
			$update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");
		}
	}
 
	public function loadPostsFriends($data, $limit) {
		
		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$str = "";
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

		if (mysqli_num_rows($data_query) > 0) {

			$num_iterations = 0; // number of results checked (not necessarily posted)
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {

				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];
				$imagePath = $row['image'];

				// prepare user_to string so it can be included even if not posted to a user
				if ($row['user_to'] == 'none'){
					$user_to = "";
				} else {
					$user_to_obj = new User($this->con, $row['user_to']);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
				}

				// check if user who posted has their account closed
				$added_by_obj = new User($this->con, $added_by);
				if ($added_by_obj->isClosed()){
					continue;
				}

				// skip posts from not friends of the logged in user
				$userLoggedInObj = new User($this->con, $userLoggedIn);
				if (!$userLoggedInObj->isFriend($added_by)) {
					continue;
				}

				// ???
				if ($num_iterations++ < $start) {
					continue;
				}

				// create delete button if the post doesn't belong to the logged in user
				if ($userLoggedIn == $added_by) {
					$deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
				} else {
					$deleteButton = "";
				}

				// once 10 posts have been loaded, break
				if ($count > $limit) {
					break;
				} else {
					$count++;
				}

				// get information of the user/owner of the post
				$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
				$user_row = mysqli_fetch_array($user_details_query);
				$first_name = $user_row['first_name'];
				$last_name = $user_row['last_name'];
				$profile_pic = $user_row['profile_pic'];

				?>

				<script>

				// toggling the comment section between visible and hide
				function toggle<?php echo $id; ?>() {

					var target = $(event.target);

					if (!target.is('a')) {

						var element = document.getElementById('toggleComment<?php echo $id; ?>');

						if (element.style.display == 'block') {
							element.style.display = 'none';
						} else {
							element.style.display = 'block';
						}
					}
				}

				</script>

				<?php

				// getting the number of comments for the post
				$commentsCheck = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id = '$id'");
				$commentsChechkNum = mysqli_num_rows($commentsCheck);

				// timeframe message
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_time);		// time of post
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

				if ($imagePath != "") {
					$imageDiv = "<div class='posted-image'>
									<img src='$imagePath' >
								 </div>";
				} else {
					$imageDiv = "";
				}

				// the post with all the finalized data to be displayed
				$str .= "<div class='status_post' onClick='javascript:toggle$id()'>

							<div class='post_profile_pic'>
								<img src='$profile_pic' width='50'>
							</div>

							<div class='posted_by' style='color:#ACACAC'>
								<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp; $time_message

								<!-- delete button to delete the post -->
								$deleteButton
							</div>

							<div id='post_body'>
								$body
								<br>
								$imageDiv
								<br>
								<br>
							</div>

							<div class='newsfeedPostOptions'>
								
								<!-- display number of comments for the post -->
								Comments($commentsChechkNum)&nbsp;&nbsp;&nbsp;

								<!-- iframe for like.php page to handle like button activity -->
								<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								
							</div>

						</div>

						<!-- iframe to handle comments for the post -->
						<div class='post_comment' id='toggleComment$id' style='display:none'>
							<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder=0></iframe>
						</div>

						<hr>";
	
				?>

				<script>
					// script to handle deleting post
					$(document).ready(function() {
						$('#post<?php echo $id; ?>').on('click', function() {
							// bootbox is additional tool downloaded from internet
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {
								// sending the data to delete_post.php page to handle the deletion
								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								// reload page once post is deleted
								if (result){
									location.reload();
								}
							});
						});
					});
				</script>

				<?php			
			
			} // end of while loop

			if ($count > $limit) {
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
				<input type='hidden' class='noMorePosts' value='false'>";
			} else {
				$str .= "<input type='hidden' class='noMorePosts' value='true'>
				<p style='text-align:centre;'> No more posts to show!</p>";
			}
		}

		echo $str;
	}

	public function loadProfilePosts($data, $limit) {
		
		$page = $data['page'];
		$profileUser = $data['profileUsername'];
		$userLoggedIn = $this->user_obj->getUsername();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$str = "";
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by = '$profileUser' AND user_to = 'none') OR user_to = '$profileUser') ORDER BY id DESC");

		if (mysqli_num_rows($data_query) > 0) {

			$num_iterations = 0; // number of results checked (not necessarily posted)
			$count = 1;

			while($row = mysqli_fetch_array($data_query)) {

				$id = $row['id'];
				$body = $row['body'];
				$added_by = $row['added_by'];
				$date_time = $row['date_added'];

				if ($num_iterations++ < $start) {
					continue;
				}

				// create delete button if the post doesn't belong to the logged in user
				if ($userLoggedIn == $added_by) {
					$deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
				} else {
					$deleteButton = "";
				}

				// once 10 posts have been loaded, break
				if ($count > $limit) {
					break;
				} else {
					$count++;
				}

				$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
				$user_row = mysqli_fetch_array($user_details_query);
				$first_name = $user_row['first_name'];
				$last_name = $user_row['last_name'];
				$profile_pic = $user_row['profile_pic'];

				?>

				<script>

				function toggle<?php echo $id; ?>() {

					var target = $(event.target);

					if (!target.is('a')) {

						var element = document.getElementById('toggleComment<?php echo $id; ?>');

						if (element.style.display == 'block') {
							element.style.display = 'none';
						} else {
							element.style.display = 'block';
						}
					}
				}

				</script>

				<?php

				$commentsCheck = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id = '$id'");
				$commentsChechkNum = mysqli_num_rows($commentsCheck);

				// timeframe
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_time);		// time of post
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

				$str .= "<div class='status_post' onClick='javascript:toggle$id()'>
							<div class='post_profile_pic'>
								<img src='$profile_pic' width='50'>
							</div>

							<div class='posted_by' style='color:#ACACAC'>
								<a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp; $time_message
								$deleteButton;
							</div>

							<div id='post_body'>
								$body
								<br>
								<br>
								<br>
							</div>

							<div class='newsfeedPostOptions'>
								Comments($commentsChechkNum)&nbsp;&nbsp;&nbsp;
								<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
							</div>
						</div>
						<div class='post_comment' id='toggleComment$id' style='display:none'>
							<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder=0></iframe>
						</div>
						<hr>";
	
				?>

				<script>
					$(document).ready(function() {
						// script to handle deleting post
						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {
								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if (result){
									location.reload();
								}
							});
						});
					});
				</script>

				<?php			
			
			} // end of while loop

			if ($count > $limit) {
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
				<input type='hidden' class='noMorePosts' value='false'>";
			} else {
				$str .= "<input type='hidden' class='noMorePosts' value='true'>
				<p style='text-align:centre;'> No more posts to show!</p>";
			}
		}

		echo $str;
	}

	public function getSinglePost($postId) {
		
		$userLoggedIn = $this->user_obj->getUsername();

		// update the notification as opened
		$openedQuery = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$postId'");

		$str = "";
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$postId'");

		if (mysqli_num_rows($data_query) > 0) {

			$row = mysqli_fetch_array($data_query);

			$id = $row['id'];
			$body = $row['body'];
			$added_by = $row['added_by'];
			$date_time = $row['date_added'];

			// prepare user_to string so it can be included even if not posted to a user
			if ($row['user_to'] == 'none'){
				$user_to = "";
			} else {
				$user_to_obj = new User($this->con, $row['user_to']);
				$user_to_name = $user_to_obj->getFirstAndLastName();
				$user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
			}

			// check if user who posted has their account closed
			$added_by_obj = new User($this->con, $added_by);
			if ($added_by_obj->isClosed()){
				echo "<p>You cannot see the post because you are not friends with this user!</p>";
				return;
			}

			// skip posts from not friends of the logged in user
			$userLoggedInObj = new User($this->con, $userLoggedIn);
			if (!$userLoggedInObj->isFriend($added_by)) {
				return;
			}

			// create delete button if the post doesn't belong to the logged in user
			if ($userLoggedIn == $added_by) {
				$deleteButton = "<button class='delete_button btn-danger' id='post$id'>X</button>";
			} else {
				$deleteButton = "";
			}

			// get information of the user/owner of the post
			$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
			$user_row = mysqli_fetch_array($user_details_query);
			$first_name = $user_row['first_name'];
			$last_name = $user_row['last_name'];
			$profile_pic = $user_row['profile_pic'];

			?>

			<script>
			// toggling the comment section between visible and hide
			function toggle<?php echo $id; ?>() {

				var target = $(event.target);

				if (!target.is('a')) {

					var element = document.getElementById('toggleComment<?php echo $id; ?>');

					if (element.style.display == 'block') {
						element.style.display = 'none';
					} else {
						element.style.display = 'block';
					}
				}
			}
			</script>

			<?php

			// getting the number of comments for the post
			$commentsCheck = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id = '$id'");
			$commentsChechkNum = mysqli_num_rows($commentsCheck);

			// timeframe message
			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($date_time);		// time of post
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

			// the post with all the finalized data to be displayed
			$str .= "<div class='status_post' onClick='javascript:toggle$id()'>

						<div class='post_profile_pic'>
							<img src='$profile_pic' width='50'>
						</div>

						<div class='posted_by' style='color:#ACACAC'>
							<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp; $time_message

							<!-- delete button to delete the post -->
							$deleteButton
						</div>

						<div id='post_body'>
							$body
							<br>
							<br>
							<br>
						</div>

						<div class='newsfeedPostOptions'>
							
							<!-- display number of comments for the post -->
							Comments($commentsChechkNum)&nbsp;&nbsp;&nbsp;

							<!-- iframe for like.php page to handle like button activity -->
							<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
							
						</div>

					</div>

					<!-- iframe to handle comments for the post -->
					<div class='post_comment' id='toggleComment$id' style='display:none'>
						<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder=0></iframe>
					</div>

					<hr>";

			?>
			<script>
				// script to handle deleting post
				$(document).ready(function() {
					$('#post<?php echo $id; ?>').on('click', function() {
						// bootbox is additional tool downloaded from internet
						bootbox.confirm("Are you sure you want to delete this post?", function(result) {
							// sending the data to delete_post.php page to handle the deletion
							$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

							// reload page once post is deleted
							if (result){
								location.reload();
							}
						});
					});
				});
			</script>
			<?php			
		} else {
			echo "<p>No post found. If you clicked a link, it may be broken</p>";
			return;
		}

		echo $str;
	}		
}

?>