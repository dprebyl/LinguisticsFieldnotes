<?php
	session_start();
	$id = $_POST["student-id"];
	$approved = ($id == "123456");
	
	if ($approved) {
		$_SESSION["approved"] = true;
		header("Location: record.php");
	} else {
		$_SESSION["approved"] = false;
		header("Location: ./");
	}
?>