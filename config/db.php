<?php
// Database connection settings
$host = "localhost";         // Change if your DB is on a different host

$username = "root";          // Your DB username
$password = "";              // Your DB password
$dbname = "student-management";
// Create connection
$conn =  mysqli_connect($host, $username, $password, $dbname);

// Check connection
if ($conn) {
   echo"You are connected";
}
else{
    echo"Could not connected";
}


?>
