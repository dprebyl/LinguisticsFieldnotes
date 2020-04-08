<?php
	require("config.php");
	requireLogin();

	$data = json_decode($_POST["data"], true);	
	$output = "";
	
	foreach ($data["entries"] as $entry) {
		$output .= "(" . $entry[0] . ") [@" . $entry[1] . "]\n"; // Number and timestamp
		for ($i = 2; $i <= 6; $i++) $output .= $entry[$i] . "\n"; // Most of the inputs
		if ($entry[7] != "") $output .= "[" . $entry[7] . "]\n"; // Comments
		$output .= "\n"; // Gap between entrties
	}
	
	$_SESSION["submission"] = $output;
	file_put_contents(NARRATIVE_FILE, $output, FILE_APPEND | LOCK_EX);
	header("Location: submission-received.php");
	
	if (NARRATIVE_FILE !== false && BACKUP_DIR !== false) {
		copy(NARRATIVE_FILE, BACKUP_DIR . "/" . date("Y-m-d_H.i.s") . "_narrative.txt"); // Create a backup
	}
?>