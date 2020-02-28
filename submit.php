<?php
	require("config.php");
	requireLogin();

	// Determine the file name based on language and semester/year
	$month = intval(date("n"));
	$semester = ($month <= 5 ? "Spring" : ($month <= 7 ? "Summer" : "Fall"));
	$FILE = PROJECT_FILE_DIR . "/" . readConfigFile("LanguageName")[0] . "Fieldnotes-" . $semester . date("Y") . ".txt";

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
	file_put_contents($FILE, $output, FILE_APPEND | LOCK_EX);
	header("Location: submission-received.php");
	
	if ($FILE !== false && BACKUP_DIR !== false) {
		copy($FILE, BACKUP_DIR . "/" . date("Y-m-d_H.i.s") . ".txt"); // Create a backup
	}
?>