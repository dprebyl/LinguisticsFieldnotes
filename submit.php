<?php
	require("config.php");
	requireLogin();

	$FILE = "TurkmenFieldnotes-Spring2020.txt";

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
	
	//echo "<pre>$output</pre>";
	file_put_contents(PROJECT_FILE, $output, FILE_APPEND | LOCK_EX);
	header("Location: submission-received.php");
	
	if (PROJECT_FILE !== false) {
		copy(PROJECT_FILE, BACKUP_DIR . "/" . date("Y-m-d_H.i.s")); // Create a backup
	}
?>