<?php
	require("config.php");
	requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Fieldnotes entry</title>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous" media="all">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous" media="all">
		<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
		<script>
			localStorage.clear();
			
			function copy() {
				document.getElementById("submission").select();
				document.execCommand("copy");
			}
			
			function download() {
				var submission = document.getElementById("submission").value;
				var txtFile = new Blob([submission], {type: "text/plain"}); // TXT file
				var downloadLink = document.createElement("a"); // Download link
				downloadLink.download = "FieldnotesSubmission_<?=date("Y-m-d")?>.txt"; // Filename
				downloadLink.href = window.URL.createObjectURL(txtFile); // Create a link to the file
				downloadLink.style.display = "none"; // Hide download link
				document.body.appendChild(downloadLink); // Add the link to DOM
				downloadLink.click(); // Click download link
			}
		</script>
	</head>
	<body>
		<div class="container">
			<h1>Submission Received</h1>
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="form-group">
						<textarea id="submission" class="form-control" rows="16" style="resize:vertical" readonly onclick="this.select()"><?=$_SESSION["submission"]?></textarea>
					</div>
					<div>
						<input type="button" class="btn btn-default col-xs-3" value="Copy" onclick="copy()">
						<input type="button" class="btn btn-default col-xs-3 pull-right" value="Download" onclick="download()">
						<div class="spacer" style="clear:both"></div>
					</div>
				</div>
			</div>
			<p><a href="./">Return to login page</a></p>
		</div>
	</body>
</html>