<?php  

$conn = mysqli_connect("localhost", "test_user", "Abcd123!@#", "social");

if (mysqli_connect_errno()) {
	echo "Failed to connect: " . mysqli_connect_errno();
} else {
	echo "Connected successfully!";
}

$query = mysqli_query($conn, "INSERT INTO test VALUES('', 'John Wick')");

?>


<!DOCTYPE html>
<html>
<head>
	<title>Facebook</title>
</head>
<body>

Hello world!!!

</body>
</html>