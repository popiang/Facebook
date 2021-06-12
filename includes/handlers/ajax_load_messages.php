<?php  
include ("../../config/config.php");
include ("../classes/User.php");
include ("../classes/Message.php");

// number of messages to load at one time
$limit = 7;

$message = new Message($conn, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropdown($_REQUEST, $limit);

?>