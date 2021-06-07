<?php  
require '../../config/config.php';

if (isset($_GET['post_id'])) {
	$postId = $_GET['post_id'];	
}

if (isset($_POST['result'])) {
	if ($_POST['result'] == 'true') {
		$deleteQuery = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$postId'");
	}
}

?>