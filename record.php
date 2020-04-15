<?php
	require("config.php");
	
	requireLogin();
	
	$languageName = readConfigFile("LanguageName")[0];
	$annotations = getAnnotations();
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
		
		<style type="text/css">
			.top-spacing { margin-top: 15px; }
			#player {
				height: 34px;
				padding: 0;
			}
			#entry-list {
				max-height: calc(100vh - 90px);
				overflow-y: auto;
			}
			#entry-list .list-group-item > div > div {
				overflow: hidden; 
				text-overflow: ellipsis; 
				white-space: nowrap;
			}
			#entry-list .list-group-item.hide-if-siblings {
				display: none;
			}
			#entry-list .list-group-item.hide-if-siblings:only-child {
				display: block;
			}
		</style>
		<script type="text/javascript">
			const GLOBAL_INPUTS = ["recording", "date", "names", "topic"]; // Apply to the whole session
			const ENTRY_INPUTS = ["foreign", "english", "comment"]; // Apply to each specific entry
			var entries = [];
			var editNum = -1;
			
			// Setup global inputs
			window.addEventListener("DOMContentLoaded", function() {
				for (var i = 0; i < GLOBAL_INPUTS.length; i++) {
					document.getElementById(GLOBAL_INPUTS[i]).value = localStorage.getItem(GLOBAL_INPUTS[i]);
				}
				// Special handling for audio select - may be duplicate values if two recordings from same date
				var recordingInput = document.getElementById("recording");
				if (recordingInput.value) setAudio(recordingInput);
			});
			
			// === Saving/loading entries =====================================
			
			// Called when user types in date/source/topic, saves to local storage and removes error if present
			function storeGlobal(el) {
				el.parentNode.classList.remove('has-error');
				localStorage.setItem(el.id, el.value);
			}
			
			// Load and display entries from localStorage
			function loadEntries() {
				var entryList = document.getElementById("entry-list");
				entryList.innerHTML = "";
				entries = JSON.parse(localStorage.getItem("entries"));
				if (entries == null) entries = [];
				for (var i = 0; i < entries.length; i++) {
					entryList.appendChild(drawListEntry(entries[i], i));
				}
				if (entries.length == 0) {
					var listItem = document.createElement("a");
					listItem.className = "list-group-item hide-if-siblings";
					listItem.href = "javascript:void(0)";
					listItem.innerText = "Entries you add will be listed here. Click on them to edit or delete. Once all entries for the session have been added, click submit.";
					entryList.appendChild(listItem);
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
				listItem.href = "javascript:void(0)";
				listItem.id = "entry" + num;
				listItem.onclick = e => toggleEdit(listItem);
				//listItem.appendChild(document.createTextNode(entry[0] + " | " + entry[1]));
				listItem.innerHTML = '<div class="row"><div class="col-xs-6">' + entry[0] + '</div><div class="col-xs-6">' + entry[1] + '</div></div>';
				return listItem;
			}
			
			// === User creating/editing entries ==============================
			
			// Return user's inputs an an array and clears the text boxes
			function getUserEntry() {
				var entry = [];
				for (var i = 0; i < ENTRY_INPUTS.length; i++) {
					entry[i] = document.getElementById(ENTRY_INPUTS[i]).value;
					document.getElementById(ENTRY_INPUTS[i]).value = "";
				}
				return entry;
			}
			
			// Ensure an entry is valid before creating or saving changes
			function validEntry() {
				var good = true;
				for (var i = 0; i < 2; i++) { // Only first two boxes required
					var el = document.getElementById(ENTRY_INPUTS[i]);
					if (el.value == "") {
						el.parentNode.classList.add("has-error");
						good = false;
					}
				}
				if (!good) { // User needs to input something
					$("#entry-error-modal").modal();
				}
				return good;
			}
			
			// User adds a new entry to the list
			function createEntry() {
				if (!validEntry()) return;
				
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
					editNum = -1;
					
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
				if (!validEntry()) return;
				
				entries[editNum] = getUserEntry();
				saveEntries();
				
				var listItem = document.getElementById("entry" + editNum);
				listItem.parentNode.replaceChild(drawListEntry(entries[editNum], editNum), listItem);
				toggleEdit(listItem);
			}
			
			// User deletes entry
			function deleteEntry() {
				var listItem = document.getElementById("entry" + editNum);
				
				entries.splice(editNum, 1);
				toggleEdit(listItem);
				
				saveEntries();
				loadEntries(); // Much easier than trying to renumber
			}
			
			// When users presses enter key within form, add/save changes and refocus first input
			function enter() {
				if (editNum == -1) createEntry();
				else saveEdit();
				document.getElementById(ENTRY_INPUTS[0]).focus();
				return false; // Prevents form from being submitted
			}
			
			// === User submitting ============================================
			
			// Ensure date, source, and topic are filled out, and there is at least one entry
			function attemptSubmit() {
				var good = entries.length > 0;
				var data = {entries: entries};
				for (var i = 1; i < GLOBAL_INPUTS.length; i++) { // Skip first input (not required)
					var el = document.getElementById(GLOBAL_INPUTS[i]);
					if (el.value == "") {
						el.parentNode.classList.add("has-error");
						good = false;
					}
					data[GLOBAL_INPUTS[i]] = el.value;
				}
				if (!good) { // User needs to input something
					$("#submission-error-modal").modal();
				} else { // Submit
					var el = document.getElementById("submission-data");
					el.value = JSON.stringify(data);
					el.parentNode.submit();
				}
			}
			
			// === IPA Input ==================================================
			
			var ipaElement = undefined;
			
			function setIpaInput(el) {
				ipaElement = el;
			}
			
			// For the comments textarea only
			function checkEnter(e) {
				if ((e.keyCode ? e.keyCode : e.which) == 13) enter();
			}
			
			function toggleIpa(btn) {
				document.getElementById("ipa-spacer").classList.toggle("hidden");
				btn.classList.toggle("btn-info");
			}
			
			// When a button is pressed on the IPA input
			function type(symbol) {
				if (ipaElement == undefined) ipaElement = document.getElementById("foreign");
				ipaElement.value += symbol;
				ipaElement.focus();
			}
			
			// === Audio ======================================================
			
			function setAudio(select) {
				storeGlobal(select);
				select.parentElement.style.display = "none";
				
				var player = document.getElementById("player");
				player.src = "<?=AUDIO_DIR?>/" + select.value;
				player.parentElement.style.display = "";
				
				var dateInput = document.getElementById("date");
				if (dateInput.value == "") {
					dateInput.value = select.options[select.selectedIndex].dataset.date;
					storeGlobal(dateInput);
				}
				
				document.getElementById("timestamp-button").classList.remove("hidden");
			}
			
			function unsetAudio() {
				var select = document.getElementById("recording");
				select.parentElement.style.display = "";
				select.selectedIndex = 0;
				storeGlobal(select);
				
				var player = document.getElementById("player");
				player.parentElement.style.display = "none";
				player.src = "";
				
				var dateInput = document.getElementById("date");
				dateInput.value = "";
				storeGlobal(dateInput);
				
				document.getElementById("timestamp-button").classList.add("hidden");
			}
			
			function addTimestamp() {
				document.getElementById("comment").value += "(" 
					+ document.getElementById("recording").value.slice(0, -4)
					+ " @ " + Math.floor(document.getElementById("player").currentTime) + "s)";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7 top-spacing">
					<form class="form-horizontal" onsubmit="enter()" action="javascript:void(0)">
						<input type="submit" class="hidden"><!-- Required for enter to submit form -->
						<div class="form-group">
							<label class="control-label col-md-2" for="recording">Recording:</label>
							<div class="col-md-10">
								<select id="recording" class="form-control" onchange="setAudio(this)">
									<option selected disabled value="">Choose a recording...</option>
									<?php
										$rawFiles = scandir(AUDIO_DIR);
										$files = [];
										foreach ($rawFiles as $file) {
											$extension = pathinfo($file)["extension"];
											if (!in_array($extension, AUDIO_EXTENSIONS)) continue; // Skip wrong file types
											$date = date_create_from_format("YMj|", explode("-", $file)[0]);
											if ($date === false) continue; // Skip files with invalid names
											$date = $date->getTimestamp();
											array_push($files, [$file, $date]);
										}
										// Sort files array by column index 1 which is the date, newest first
										array_multisort(array_column($files, 1), SORT_DESC, $files);
										foreach ($files as $file) {
											echo '<option value="' . $file[0] . '" data-date="' . date("Y-m-d", $file[1]) . '">' 
												. pathinfo($file[0])["filename"] . "</option>";
										}
									?>
								</select>
							</div>
							<div class="col-md-10" style="display:none">
								<audio id="player" class="col-xs-11" controls controlslist="nodownload"></audio>
								<button type="button" class="btn btn-default col-xs-1" tabindex="-1" title="Select different audio file" onclick="unsetAudio()">
									<span class="glyphicon glyphicon-remove"></span>
								</button>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="date">Date:</label>
							<div class="col-md-10">
								<input type="date" class="form-control" id="date" placeholder="Format like YYYY-MM-DD, ex: <?=date("Y-m-d")?>" oninput="storeGlobal(this)">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="names">Source:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="names" placeholder="Class elicitation (typed by John Gluckman)" oninput="storeGlobal(this)">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="topic">Topic:</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="topic" placeholder="Swadesh list" oninput="storeGlobal(this)">
							</div>
						</div>
						<hr>
						<div id="editor">
							<div class="form-group">
								<label class="control-label col-md-2" for="foreign"><?=$languageName?>:</label>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" class="form-control" id="foreign" onfocus="setIpaInput(this)" oninput="this.parentNode.classList.remove('has-error')">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default" tabindex="-1" title="Enter IPA symbols" onclick="toggleIpa(this)">
												<span class="glyphicon glyphicon-pencil"></span>
											</button>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-2" for="english">Translation:</label>
								<div class="col-md-10">
									<input type="text" class="form-control" id="english" oninput="this.parentNode.classList.remove('has-error')">
								</div>
							</div>
							<div class="form-group">
							  <label class="control-label col-md-2" for="comment">Comments (optional):</label>
							  <div class="col-md-10">
								<textarea class="form-control" rows="3" id="comment" style="resize:vertical" onkeypress="checkEnter(event)" onfocus="setIpaInput(this)"></textarea>
							  </div>
							</div>
							<div class="form-group">
								<div id="timestamp-button" class="col-md-3 col-sm-2 col-xs-3 hidden">
									<button type="button" class="btn btn-default col-xs-12" onclick="addTimestamp()">Timestamp</button>
								</div>
								<div id="add-buttons">
									<div class="col-md-3 col-sm-2 col-xs-3 pull-right">
										<button type="button" class="btn btn-success col-xs-12" onclick="createEntry()">Add</button>
									</div>
								</div>
								<div id="edit-buttons" class="hidden">
									<div class="col-md-3 col-sm-2 col-xs-3 pull-right">
										<button type="button" class="btn btn-success col-xs-12" onclick="saveEdit()">Save</button>
									</div>
									<div class="col-md-3 col-sm-2 col-xs-3 pull-right">
										<button type="button" class="btn btn-danger col-xs-12" onclick="deleteEntry()">Delete</button>
									</div>
								</div>
							</div>
						</div>
					</form>
					<div class="panel-group">
						<div class="panel panel-info">
							<div class="panel-heading">Annotations</div>
							<div class="panel-body"><?=$annotations?></div>
						</div>
					</div>
					<p>Tip: For fast entry, press tab to move between textboxes and enter to add/save the current entry.</p>
				</div>
				<div class="col-md-5 top-spacing">
					<div class="list-group" id="entry-list">
						<script type="text/javascript">loadEntries();</script>
					</div>
					<form class="form-horizontal">
						<div class="form-group">
							<div class="col-md-4 col-sm-2 col-xs-3 pull-right">
								<button type="button" class="btn btn-success col-xs-12" onclick="attemptSubmit()">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		<!--- IPA input -->
		<?php require("ipa-input.php"); ?>
		
		<!-- Modals for error -->
		<div class="modal fade" id="entry-error-modal" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Incomplete entry</h4>
						</div>
					<div class="modal-body">
						<p>Please ensure you have entered something for both <?=$languageName?> and Translation.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="submission-error-modal" role="dialog">
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
		
		<!-- Hidden form used to submit data -->
		<form method="POST" action="submit.php" style="display:none">
			<input type="hidden" name="data" id="submission-data">
		</form>
	</body>
</html>