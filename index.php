<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
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
			const GLOBAL_INPUTS = ["date", "names", "topic"]; // Apply to the whole session
			const ENTRY_INPUTS = ["foreign", "english", "comment"]; // Apply to each specific entry
			var entries = [];
			var editNum = 0;
			
			// Load and display entries from localStorage
			function loadEntries() {
				var entryList = document.getElementById("entry-list");
				entryList.innerHTML = "";
				entries = JSON.parse(localStorage.getItem("entries"));
				if (entries == null) entries = [];
				for (var i = 0; i < entries.length; i++) {
					entryList.appendChild(drawListEntry(entries[i], i));
				}
			}
			
			// Called whenever the user adds, edits, or deletes an entry
			function saveEntries() {
				localStorage.setItem("entries", JSON.stringify(entries));
			}
			
			// Adds an element to the right column list
			function drawListEntry(entry, num) {
				var listItem = document.createElement("a");
				listItem.className = "list-group-item";
				listItem.href = "#";
				listItem.id = "entry" + num;
				listItem.onclick = e => toggleEdit(listItem);
				var text = document.createTextNode(entry[0] + " / " + entry[1]);
				listItem.appendChild(text);
				return listItem;
			}
			
			// Return user's inputs an an array and clears the text boxes
			function getUserEntry() {
				var entry = [];
				for (var i = 0; i < ENTRY_INPUTS.length; i++) {
					entry[i] = document.getElementById(ENTRY_INPUTS[i]).value;
					document.getElementById(ENTRY_INPUTS[i]).value = "";
				}
				return entry;
			}
			
			// User adds a new entry to the list
			function createEntry() {
				// Read inputs from user and add to local storage
				var entry = getUserEntry();
				entries.push(entry);
				saveEntries();

				// display in right column
				document.getElementById("entry-list").appendChild(
					drawListEntry(entry, entries.length-1)
				);
			}
			
			// Enter/exit editing mode (as opposed to adding mode)
			function toggleEdit(item) {
				// Currently editing this entry - stop editing it (without saving)
				if (item.classList.contains("active")) {
					document.getElementById("editor").classList.remove("well");
					document.getElementById("add-buttons").classList.remove("hidden");
					document.getElementById("edit-buttons").classList.add("hidden");
					item.classList.remove("active");
					
					for (var i = 0; i < ENTRY_INPUTS.length; i++) {
						document.getElementById(ENTRY_INPUTS[i]).value = "";
					}
					
				} else { // Open this entry for editing
					document.getElementById("editor").classList.add("well");
					document.getElementById("add-buttons").classList.add("hidden");
					document.getElementById("edit-buttons").classList.remove("hidden");
					
					// Deselect any other entries
					document.querySelectorAll("#entry-list a").forEach(el => el.classList.remove("active"));
					item.classList.add("active");
					
					// Put text in editor
					editNum = +item.id.substr(5);
					for (var i = 0; i < ENTRY_INPUTS.length; i++) {
						document.getElementById(ENTRY_INPUTS[i]).value = entries[editNum][i];
					}
				}
			}
			
			// User saves changes to entry being edited
			function saveEdit() {
				entries[editNum] = getUserEntry();
				saveEntries();
				
				var listItem = document.getElementById("entry" + editNum);
				toggleEdit(listItem);
				listItem.parentNode.replaceChild(drawListEntry(entries[editNum], editNum), listItem);
			}
			
			// User deletes entry
			function deleteEntry() {
				var listItem = document.getElementById("entry" + editNum);
				toggleEdit(listItem);
				
				entries.splice(editNum, 1);
				saveEntries();
				loadEntries(); // Much easier than trying to renumber
			}
			
			// Ensure date, source, and topic are filled out, and there is at least one entry
			function attemptSubmit() {
				var good = entries.length > 0;
				var data = {entries: entries};
				for (var i = 0; i < GLOBAL_INPUTS.length; i++) {
					var el = document.getElementById(GLOBAL_INPUTS[i]);
					if (el.value == "") {
						el.parentNode.classList.add("has-error");
						good = false;
					}
					data[GLOBAL_INPUTS[i]] = el.value;
				}
				if (!good) { // User needs to input something
					$("#error-modal").modal();
				} else { // Submit
					var el = document.getElementById("submission-data");
					el.value = JSON.stringify(data);
					el.parentNode.submit();
				}
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
								<input type="date" class="form-control" id="date" oninput="this.parentNode.classList.remove('has-error')">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="names">Source:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="names" placeholder="Class elicitation (typed by John Gluckman)" oninput="this.parentNode.classList.remove('has-error')">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="topic">Topic:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="topic" placeholder="Swadesh list" oninput="this.parentNode.classList.remove('has-error')">
							</div>
						</div>
						<hr>
						<div id="editor">
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
							<div class="form-group" id="add-buttons">
								<div class="col-md-2 pull-right">
									<button type="button" class="btn btn-success col-md-12" onclick="createEntry()">Add</button>
								</div>
							</div>
							<div class="form-group hidden" id="edit-buttons">
								<div class="col-md-2">
									<button type="button" class="btn btn-danger col-md-12" onclick="deleteEntry()">Delete</button>
								</div>
								<div class="col-md-2 pull-right">
									<button type="button" class="btn btn-success col-md-12" onclick="saveEdit()">Save</button>
								</div>
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
					<div class="list-group" id="entry-list">
						<script type="text/javascript">loadEntries();</script>
					</div>
					<form class="form-horizontal">
						<div class="form-group">
							<div class="col-md-3 pull-right">
								<button type="button" class="btn btn-success col-md-12" onclick="attemptSubmit()">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		<!-- Modal -->
		<div class="modal fade" id="error-modal" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Incomplete submission</h4>
						</div>
					<div class="modal-body">
						<p>Please ensure you have entered a date, source, topic, and at least one entry.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<form method="POST" action="submit.php" style="display:none">
			<input type="hidden" name="data" id="submission-data">
		</form>
	</body>
</html>