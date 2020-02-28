<?php
	require("config.php");
	
	session_start();
	$_SESSION["timedout"] = false;
	
	$id = $_POST["student-id"];
	
	if (verifyId($id)) {
		$_SESSION["approved"] = true;
		header("Location: record.php");
	} else {
		$_SESSION["approved"] = false;
		header("Location: ./");
	}
?>