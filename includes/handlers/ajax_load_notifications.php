<?php
include "../../config/config.php";
include "../classes/User.php";
include "../classes/Notification.php";
include "../classes/Message.php";

// number of notifications to load at one time
$limit = 7;

$notification = new Notification($conn, $_REQUEST['userLoggedIn']);
echo $notification->getNotifications($_REQUEST, $limit);

?>