<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Field notes entry</title>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous" media="all">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous" media="all">
		<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
		
		<style type="text/css">
			.list-group-item {
				overflow: hidden; 
				text-overflow: ellipsis; 
				white-space: nowrap;
			}
		</style>
		<script type="text/javascript">
			const ENTRY_INPUTS = ["foreign", "english", "comment"];
			
			
			function add() {
				// Read inputs from user
				var entry = [];
				for (var i = 0; i < ENTRY_INPUTS.length; i++) {
					entry[i] = document.getElementById(ENTRY_INPUTS[i]).value;
				}
				console.log(entry);
				
				// Add to local storage
				var entries = JSON.parse(localStorage.getItem("entries"));
				if (entries == null) entries = [];
				entries.push(entry);
				localStorage.setItem("entries", JSON.stringify(entries));
				console.log(entries);
				
				// display in right column
				createListEntry(entry);
			}
			
			// Adds an element to the right column list
			function createListEntry(entry) {
				var listItem = document.createElement("li");
				listItem.className = "list-group-item";
				var text = document.createTextNode(entry[0] + " / " + entry[1]);
				listItem.appendChild(text);
				document.getElementById("entry-list").appendChild(listItem);
			}
		</script>
	</head>
	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7">
					<h1>Field note entry</h1>
					<form class="form-horizontal">
						<div class="form-group">
							<label class="control-label col-md-2" for="date">Date:</label>
							<div class="col-md-10">
								<input type="date" class="form-control" id="date">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="names">Source:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="names" placeholder="Class elicitation (typed by John Gluckman)">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="topic">Topic:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="topic" placeholder="Swadesh list">
							</div>
						</div>
						<hr>
						<div class="form-group">
							<label class="control-label col-md-2" for="foreign">Foreign:</label>
							<div class="col-md-10">
								<div class="input-group">
									<input type="text" class="form-control" id="foreign">
									<div class="input-group-btn">
										<button type="button" class="btn btn-default">
											<span class="glyphicon glyphicon-pencil"></span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="english">English:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="english">
							</div>
						</div>
						<div class="form-group">
						  <label class="control-label col-md-2" for="comment">Comments (optional):</label>
						  <div class="col-md-10">
							<textarea class="form-control" rows="3" id="comment" style="resize:vertical"></textarea>
						  </div>
						</div>
						<div class="form-group">
							<div class="col-md-2">
								<button type="button" class="btn btn-success col-md-12" onclick="add()">Add</button>
							</div>
						</div>
					</form>
					<div class="panel-group">
						<div class="panel panel-info">
							<div class="panel-heading">Annotations</div>
							<div class="panel-body">$MP$ is for "minimal pair"</div>
						</div>
					</div>
				</div>
				<div class="col-md-5">
					<ul class="list-group" id="entry-list">
						<!--
						<li class="list-group-item">garen / stomach</li>
						<li class="list-group-item">dal / bark</li>
						<li class="list-group-item">it / dog</li>
						<li class="list-group-item">First item</li>
						<li class="list-group-item">This is an entry with a really long text to see if it can automatically put an elipses on the end of it. Wow is that really not enough text?</li>
						<li class="list-group-item">Third item</li>
						<li class="list-group-item">First item</li>
						<li class="list-group-item active">This is how editing would look</li>
						<li class="list-group-item">Third item</li>
						<li class="list-group-item">First item</li>
						<li class="list-group-item">Second item</li>
						<li class="list-group-item">Third item</li>
						<li class="list-group-item">First item</li>
						<li class="list-group-item">Second item</li>
						<li class="list-group-item">Third item</li>
						<li class="list-group-item">First item</li>
						<li class="list-group-item">Second item</li>
						<li class="list-group-item">Third item</li>
						-->
						<script type="text/javascript">
							var entries = JSON.parse(localStorage.getItem("entries"));
							if (entries == null) entries = [];
							for (var i = 0; i < entries.length; i++) {
								createListEntry(entries[i]);
							}
						</script>
					</ul>
					<form class="form-horizontal">
						<div class="form-group">
							<div class="col-md-3 pull-right">
								<button type="button" class="btn btn-success col-md-12">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>