<?php  

include "../../config/config.php";
include "../classes/User.php";
include "../classes/Post.php";

// number of posts to be loaded per call
$limit = 10; 

$posts = new Post($conn, $_REQUEST['userLoggedIn']);
$posts->loadPostsFriends($_REQUEST, $limit);

?>