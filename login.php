<?php
	require("config.php");
	
	$VALID_REDIRECTS = ["record.php", "record-narrative.php"];
	
	session_start();
	$_SESSION["timedout"] = false;
	
	$id = $_POST["student-id"];
	
	if (verifyId($id)) {
		$_SESSION["approved"] = true;
		if (in_array($_POST["redirect"], $VALID_REDIRECTS)) {
			header("Location: " . $_POST["redirect"]);
		} else {
			http500();
		}
		
	} else {
		$_SESSION["approved"] = false;
		header("Location: ./");
	}
?>