<?php
	
	$host = "localhost"; 
	$user = "m1022009"; 
	$pass = "m1022009"; 
	$db = "light"; 	   	
	$con=mysqli_connect($host, $user, $pass,$db) or die("Unable to connect!"); 
	$result = mysqli_select_db($con, $db) or die("無法選取資料庫");//選擇資料庫 
?>
