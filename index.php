<?php 
include("includes/header.php"); 

if (isset($_POST['post'])) {

	$uploadOk = 1;
	$imageName = $_FILES['fileToUpload']['name'];
	$errorMessage = "";

	if ($imageName != "") {
		$targetDir = "assets/images/posts/";
		$imageName = $targetDir . uniqid() . basename($imageName);
		$imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

		if ($_FILES['fileToUpload']['size'] > 10000000) {
			$errorMessage = "Sorry your file is too large!";
			$uploadOk = 0;
		}

		if (strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
			$errorMessage = "Sorry, only jpeg, jpg & png files are allowed!";
			$uploadOk = 0;
		}

		if ($uploadOk){
			if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
				// image uploaded
			} else {
				// image not uploaded
				$uploadOk = 0;
			}
		}
	}

	if ($uploadOk) {
		$post = new Post($conn, $userLoggedIn);
		$post->submitPost($_POST['post_text'], 'none', $imageName);
		header("Location: index.php"); // redirect back the page to fix insert data issue when refresh page
	} else {
		echo "<div style='text-align:center;' class='alert alert-danger'>
				$errorMessage;
			  </div>";
	}
}
?>

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

	<div class="main_column column">

		<form action="index.php" method="POST" class="post_form" enctype="multipart/form-data">
			<input type="file" name="fileToUpload" id="fileToUpload">
			<textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
			<input type="submit" name="post" id="post_button" value="Post">
			<hr>
		</form>

		<!-- this div will contain all the posts -->
		<div class="post_area"></div>
		<img id="loading" src="assets/images/icons/loading.gif">
		
	</div>

	<script>
		
		// handling infinite scrolling	
		$(function(){

			var userLoggedIn = '<?php echo $userLoggedIn; ?>';
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

				// display the loading gif
				$('#loading').show();

				// if nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'
				var page = $('.post_area').find('.nextPage').val() || 1; 
				
				$.ajax({
					url: "includes/handlers/ajax_load_posts.php",
					type: "POST",
					data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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