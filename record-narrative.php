<?php
	require("config.php");
	
	requireLogin();
	
	$languageName = readConfigFile("LanguageName")[0];
	
	$inputs = [
		"literal" => "What said",
		"corrected" => "Corrected", 
		"morpheme" => "Morphemes",
		"glossing" => "Glossing",
		"translation" => "Translation",
		"comments" => "Comments"
	];
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
			#entry-list .list-group-item {
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
			// Apply to each specific entry
			const ENTRY_INPUTS = ["number", "timestamp", "literal", "corrected", 
				"morpheme", "glossing", "translation", "comments"]; 
				
			var entries = [];
			var editNum = -1;
			
			var currentNum = 1; // The number for the newest entry
			
			// Setup audio selector
			window.addEventListener("DOMContentLoaded", function() {
				var recording = localStorage.getItem("recording");
				if (recording) {
					document.getElementById("recording").value = recording;
					setAudio(document.getElementById("recording"));
				}
			});
			// === Saving/loading entries =====================================
			
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
					listItem.innerText = "Examples you add will be listed here. Click on them to edit or delete. Once all entries for the session have been added, click submit.";
					entryList.appendChild(listItem);
				} else {
					// Set currentNum to one higher than last recorded entry
					currentNum = parseInt(entries[entries.length-1][0]) + 1;
					document.getElementById("number").value = currentNum;
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
				listItem.innerHTML = '(' + entry[0] + ') [@' + entry[1] + '] ' + entry[2];
				return listItem;
			}
			
			// === User creating/editing entries ==============================
			
			// Return user's inputs as an array and clears the text boxes
			function getUserEntry() {
				var entry = [];
				for (var i = 0; i < ENTRY_INPUTS.length; i++) {
					entry[i] = document.getElementById(ENTRY_INPUTS[i]).value;
					document.getElementById(ENTRY_INPUTS[i]).value = "";
				}
				document.getElementById("number").value = currentNum;
				return entry;
			}
			
			// Ensure an entry is valid before creating or saving changes
			function validEntry() {
				var good = true;
				for (var i = 0; i < ENTRY_INPUTS.length-1; i++) { // Last box (comments) optional
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
				currentNum = parseInt(document.getElementById("number").value) + 1;
				
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
					
					document.getElementById("number").value = currentNum;
					for (var i = 1; i < ENTRY_INPUTS.length; i++) {
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
			
			// Ensure there is at least one entry
			function attemptSubmit() {
				var data = {recording: document.getElementById("recording").value, entries: entries};console.log(data);
				if (entries.length == 0 || data.recording == "") { // User needs to input something
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
			
			function toggleIpa(btn) {
				document.getElementById("ipa-spacer").classList.toggle("hidden");
				btn.classList.toggle("btn-info");
			}
			
			// When a button is pressed on the IPA input
			function type(symbol) {
				if (ipaElement == undefined) ipaElement = document.getElementById("literal");
				ipaElement.value += symbol;
				ipaElement.focus();
			}
			
			// === Audio ======================================================

			function setAudio(select) {
				localStorage.setItem("recording", select.value);
				select.parentElement.style.display = "none";
				
				var player = document.getElementById("player");
				player.src = "<?=AUDIO_DIR?>/" + select.value;
				player.parentElement.style.display = "";
			}
			
			function unsetAudio() {
				var select = document.getElementById("recording");
				select.parentElement.style.display = "";
				select.selectedIndex = 0;
				localStorage.setItem("recording", "");
				
				var player = document.getElementById("player");
				player.parentElement.style.display = "none";
				player.src = "";
			}
			
			function setTimestamp() {
				document.getElementById("timestamp").value = Math.floor(document.getElementById("player").currentTime) + "s";
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
							<label class="control-label col-md-2" for="recording">Narrative:</label>
							<div class="col-md-10">
								<select id="recording" class="form-control" onchange="setAudio(this)">
									<option selected disabled value="">Choose a narrative...</option>
									<?php
										$rawFiles = scandir(AUDIO_DIR);
										$files = [];
										foreach ($rawFiles as $file) {
											$extension = substr($file, -4); // Note: Extension length hard-coded
											if (!in_array($extension, [".wav", ".mp3", ".m4a"])) continue; // Skip wrong file types
											if (strtoupper(substr($file, 0, 4)) !== "NARR") continue; // Skip files with invalid names
											echo '<option value="' . $file . '">' . substr($file, 0, -4) . "</option>";
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
						<hr>
						<div id="editor">
							<div class="form-group">
								<label class="control-label col-md-2" for="number">Number:</label>
								<div class="col-md-4">
									<input type="number" min=1 class="form-control" id="number" value="1" oninput="this.parentNode.classList.remove('has-error')">
								</div>
								<label class="control-label col-md-2" for="timestamp">Timestamp:</label>
								<div class="col-md-4">
									<div class="input-group">
										<input type="text" min=1 class="form-control" id="timestamp" oninput="this.parentNode.classList.remove('has-error')">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default" tabindex="-1" title="Update timestamp" onclick="setTimestamp()">
												<span class="glyphicon glyphicon-refresh"></span>
											</button>
										</div>
									</div>
								</div>
							</div>
							<?php foreach ($inputs as $id => $label): ?>
							<div class="form-group">
								<label class="control-label col-md-2" for="<?=$id?>"><?=$label?>:</label>
								<div class="col-md-10">
									<input type="text" class="form-control" id="<?=$id?>" onfocus="setIpaInput(this)" oninput="this.parentNode.classList.remove('has-error')">
								</div>
							</div>
							<?php endforeach; ?>
							<div class="form-group">
								<div id="timestamp-button" class="col-md-3 col-sm-2 col-xs-3">
									<button type="button" class="btn btn-default col-xs-12" tabindex="-1" onclick="toggleIpa(this)">IPA Keyboard</button>
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
						<h4 class="modal-title">Incomplete example</h4>
						</div>
					<div class="modal-body">
						<p>Please ensure you have entered something in every box (except comments).</p>
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
						<p>Please ensure you have selected a narrative and added at least one example.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Hidden form used to submit data -->
		<form method="POST" action="submit-narrative.php" style="display:none">
			<input type="hidden" name="data" id="submission-data">
		</form>
	</body>
</html>