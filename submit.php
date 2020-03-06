<?php
	require("config.php");
	requireLogin();

	$data = json_decode($_POST["data"], true);	
	$output = "";
	
	$output .= "#" . date("M d, Y", strtotime($data["date"])) . "\n"; // TODO: Should date have leading 0?
	$output .= "#" . $data["names"] . "\n";
	$output .= "#" . $data["topic"] . "\n";
	
	$output .= "\n";
	
	foreach ($data["entries"] as $entry) {
		$output .= $entry[0] . "\n" . $entry[1] . "\n";
		if ($entry[2] != "") {
			$output .= "[" . $entry[2] . "]\n";
		}
		$output .= "\n";
	}
	
	$_SESSION["submission"] = $output;
	file_put_contents(FIELDNOTES_FILE, $output, FILE_APPEND | LOCK_EX);
	header("Location: submission-received.php");
	
	if (FIELDNOTES_FILE !== false && BACKUP_DIR !== false) {
		copy(FIELDNOTES_FILE, BACKUP_DIR . "/" . date("Y-m-d_H.i.s") . ".txt"); // Create a backup
	}
?>