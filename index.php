<?php 
	require("config.php");
	session_start();
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
		<style>
			.panel { 
				margin-top: 30px;
			}
			.panel-body {
				padding-top: 0;
			}
			.alert {
				margin-top: 20px;
				margin-bottom: 0;
			}
		</style>
	</head>
	<body>
		<div class="container text-center">
			<div class="panel panel-default">
				<div class="panel-body">
					<h1>Fieldmethods login</h1>
					<form class="form-inline" method="POST" action="login.php">
						<div class="form-group">
							<label class="control-label" for="student-id"><?=LOGIN_PROMPT?>:</label>
							<input type="text" class="form-control" id="student-id" name="student-id">
							<script>
								document.getElementById("student-id").addEventListener("keypress", function(event) {
									if (event.keyCode == 13) {
										event.preventDefault();
										document.getElementById("default-submit").click();
									}
								});
							</script>
						</div>
						<br>
						<div class="form-group">
							<button type="submit" name="redirect" value="record-narrative.php" class="btn btn-default">Record Narrative</button>
							<button type="submit" name="redirect" id="default-submit" value="record.php" class="btn btn-primary">Record Fieldnotes</button>
						</div>
					</form>
					<?php if (isset($_SESSION["approved"]) && $_SESSION["approved"] == false): ?>
						<div class="alert alert-danger">
							<strong>Invalid <?=LOGIN_PROMPT?>.</strong> Contact your professor for assistance.
						</div>
					<?php elseif (isset($_SESSION["timedout"]) && $_SESSION["timedout"] == true): 
						$_SESSION["timedout"] = false; ?>
						<div class="alert alert-info">
							<strong>Login timed out.</strong> Please log back in to continue adding entries and/or submit.
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>