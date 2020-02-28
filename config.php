<?php
	// Configuration file stored one directory above web root
	$config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../FieldnotesConfig.ini");
	if ($config === false) http500();
	foreach (["CONFIG_DIR", "PROJECT_FILE_DIR", "BACKUP_DIR"] as $dir) {
		if (!array_key_exists($dir, $config)) http500();
		define($dir, $config[$dir]);
	}

	function requireLogin() {
		session_start();
		if (!isset($_SESSION["approved"]) || $_SESSION["approved"] !== true) {
			$_SESSION["timedout"] = true;
			header("Location: ./");
			exit;
		}
	}
	
	function verifyId($id) {
		return in_array($id, readConfigFile("StudentIDs"), true);
	}
	
	function getAnnotations() {
		return implode("<br>", readConfigFile("Annotations"));
	}
	
	// Returns a config file as an array of lines
	function readConfigFile($file) {
		$file = file_get_contents(CONFIG_DIR . "/" . $file . ".txt");
		if ($file === false) http500();
		$lines = explode("\n", $file);
		
		foreach ($lines as $i => $line) {
			// Remove lines that are empty (only whitespace) or begin with a # (comment)
			if ($line == "" || ctype_space($line) || $line[0] == "#") {
				unset($lines[$i]);
			} else {
				// Remove undesired characters
				$lines[$i] = str_replace(["\n", "\t", "\r"], '', $line); 
			}
		}
		return array_values($lines);
	}
	
	function http500() {
		header("HTTP/1.1 500 Internal Server Error");
		exit;
	}
?>