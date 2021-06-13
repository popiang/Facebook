<?php  
require 'config/config.php';
include "classes/Message.php";
include "classes/User.php";
include "classes/Post.php";
include "classes/Notification.php";

if (isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username'];
	$user_details_query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query);
} else {
	header("Location: register.php");
}

?>

<!DOCTYPE html>
<html>

<head>

	<title>Facebook</title>

	<!-- CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" 
	integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/css/jquery.Jcrop.css">
	<link rel="stylesheet" href="assets/css/style.css">

	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
	<script src="https://kit.fontawesome.com/c814600906.js" crossorigin="anonymous"></script>
	<script src="assets/js/bootbox.all.min.js"></script>
	<script src="assets/js/jcrop_bits.js"></script>
	<script src="assets/js/jquery.Jcrop.js"></script>
	<script src="assets/js/facebook.js"></script>

</head>

<body>

	<div class="top_bar">

		<div class="logo">
			<a href="index.php">Facebook</a>
		</div>

		<div class="search">

			<form action="search.php" method="GET" name="search_form">
				<input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search..." autocomplete="off" id="search_text_input">

				<div class="button_holder">
					<img src="assets/images/icons/magnifying_glass.png">
				</div>
			</form>

			<div class="search_results">

			</div>

			<div class="search_results_footer_empty">

			</div>

		</div>
		
		<nav>

			<?php  
			// unread messages
			$messages = new Message($conn, $userLoggedIn);
			$numMessages = $messages->getUnreadNumber();

			// unread notifications
			$notifications = new Notification($conn, $userLoggedIn);
			$numNotifications = $notifications->getUnreadNumber();

			// number of friend requests
			$userObj = new User($conn, $userLoggedIn);
			$numFriendRequest = $userObj->getNumberOfFriendRequests();
			?>

			<a href="<?php echo $userLoggedIn; ?>">
				<?php echo $user['first_name']; ?>
			</a>
			<a href="#" class="ml-3">
				<li class="fas fa-home fa-lg"></li>
			</a>
			<a href="javascript:void(0);" class="ml-3" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
				<li class="fas fa-envelope fa-lg"></li>
				<?php echo ($numMessages > 0 ? "<span class='notification_badge' id='unread_message'>$numMessages</span>" : ""); ?>
			</a>
			<a href="javascript:void(0);" class="ml-3" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
				<li class="fas fa-bell fa-lg"></li>
				<?php echo ($numNotifications > 0 ? "<span class='notification_badge' id='unread_notification'>$numNotifications</span>" : ""); ?>
			</a>
			<a href="requests.php" class="ml-3">
				<li class="fas fa-users fa-lg"></li>
				<?php echo ($numFriendRequest > 0 ? "<span class='notification_badge' id='unread_request'>$numFriendRequest</span>" : ""); ?>
			</a>
			<a href="#" class="ml-3">
				<li class="fas fa-cog fa-lg"></li>
			</a>
			<a href="includes/handlers/logout.php" class="ml-3 mr-2">
				<li class="fas fa-sign-out-alt fa-lg"></li>
			</a>
			
		</nav>

		<!-- this div is where the dropdown menu will be displayed -->
		<div class="dropdown_data_window" style="height:0px;border:none;"></div>
		<input type="hidden" id="dropdown_data_type" value="">

	</div>

	<script>
		
		// infinite scrolling in dropdown menu
		$(function(){
	 
			var userLoggedIn = '<?php echo $userLoggedIn; ?>';
			var dropdownInProgress = false;
	 
	 		// this function will be triggered when user scroll down the dropdown menu
		    $(".dropdown_data_window").scroll(function() {

		    	// this will get the last message in the current dropdown menu
		    	var bottomElement = $(".dropdown_data_window a").last();

		    	// this will get the value of hidden input 'noMoreDropdownData' to indicate there's no more messages left to display
				var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();
	 
	 			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		        // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. //
		        // the jQuery equivalent is using [0] as shown below.															//
		        //																												//
		        // load post if the element(last message) is in view and there's still data to display							//
		        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		        if (isElementInView(bottomElement[0]) && noMoreData == 'false') {
		            loadPosts();
		        }
		    });
	 
		    function loadPosts() {

		    	// if it is already in the process of loading some posts, just return
		        if(dropdownInProgress) { 
					return;
				}
				
				dropdownInProgress = true;
	 
	 			// if .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'
				var page = $('.dropdown_data_window').find('.nextPageDropDownData').val() || 1; 
	 
	 			// holds name of page to send ajax request to
				var pageName; 
				var type = $('#dropdown_data_type').val();
	 
				if(type == 'notification')
					pageName = "ajax_load_notifications.php";
				else if(type == 'message')
					pageName = "ajax_load_messages.php";
	 
				$.ajax({
					url: "includes/handlers/" + pageName,
					type: "POST",
					data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
					cache:false,
	 
					success: function(response) {
	 
	 					// removes current .nextPageDropdownData if available before appending the new result
						$('.dropdown_data_window').find('.nextPageDropdownData').remove(); 

						// removes current .noMoreDropdownData if available before appending the new result
						$('.dropdown_data_window').find('.noMoreDropdownData').remove();

						// appending the new messages/response to the existing .dropdown_data_window
						$('.dropdown_data_window').append(response);
	 
						dropdownInProgress = false;
					}
				});
		    }
	 
		    // check if the element is in view
		    function isElementInView (el) {
		    	
		    	if(el == null)
      				return;
		        
		        var rect = el.getBoundingClientRect();
	 
		        return rect.top <= document.querySelector('.dropdown_data_window').clientHeight;
		    }
		});		

	</script>

	<div class="wrapper">
