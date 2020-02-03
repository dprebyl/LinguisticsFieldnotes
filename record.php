<?php
	require("config.php");
	requireLogin();
?>
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
			.list-group { margin-top: 15px; }
			.list-group-item {
				overflow: hidden; 
				text-overflow: ellipsis; 
				white-space: nowrap;
			}
			#ipa-spacer {
				height: 240px;
			}
			#ipa {
				position: fixed;
				left: 0;
				bottom: 0;
				width: 100%;
				background: white;
				border-top: 1px solid #ddd;
				z-index: 1000;
				white-space: nowrap;
				overflow-x: auto;
				text-align: center;
				line-height: 16px;
			}
			#consonants, #vowels {
				display: inline-block;
			}
			#ipa table {
				margin-bottom: 2px;
			}
			#ipa table th, #ipa table td {
				padding: 0 4px 0 4px;
				border: 1px solid black;
				min-width: 24px;
			}
			#ipa table th {
				text-align: right;
			}
			#ipa table tr:nth-of-type(1) th {
				line-height: 16px;
				text-align: center;
			}
			#ipa table td {
				font-size: 20px;
				text-align: center;
				height: 24px;
			}
			#ipa .typable:hover {
				background: yellow;
				cursor: pointer;
			}
			#ipa .gray {
				background: gray;
			}
			#vowels {
				position: relative;
				background-image: url(vowels.svg);
				background-size: contain;
				width: 305px;
				height: 225px;
			}
			.vowel {
				position: absolute;
				border: 1px solid black;
				border-radius: 2px;
				background: white;
				font-size: 20px;
				line-height: 20px;
				width: 20px;
				text-align: center;
			}
		</style>
		<script type="text/javascript">
			const GLOBAL_INPUTS = ["date", "names", "topic"]; // Apply to the whole session
			const ENTRY_INPUTS = ["foreign", "english", "comment"]; // Apply to each specific entry
			var entries = [];
			var editNum = -1;
			
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
			
			// When users presses enter key within form, add/save changes and refocus first input
			function enter() {
				if (editNum == -1) createEntry();
				else saveEdit();
				document.getElementById(ENTRY_INPUTS[0]).focus();
				return false; // Prevents form from being submitted
			}
			
			// For the comments textarea
			function checkEnter(e) {
				if ((e.keyCode ? e.keyCode : e.which) == 13) enter();
			}
			
			function toggleIpa() {
				document.getElementById("ipa-spacer").classList.toggle("hidden");
			}
			
			// IPA keyboard
			function type(el) { 
				var foreign = document.getElementById("foreign");
				foreign.value += el.innerText;
				foreign.focus();
			}
		</script>
	</head>
	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7">
					<h1>Field note entry</h1>
					<form class="form-horizontal" onsubmit="enter()" action="javascript:void(0)">
						<input type="submit" class="hidden">
						<div class="form-group">
							<label class="control-label col-md-2" for="date">Date:</label>
							<div class="col-md-10">
								<input type="date" class="form-control" id="date" oninput="storeGlobal(this)">
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
						<script>
							for (var i = 0; i < GLOBAL_INPUTS.length; i++) document.getElementById(GLOBAL_INPUTS[i]).value = localStorage.getItem(GLOBAL_INPUTS[i]);
						</script>
						<hr>
						<div id="editor">
							<div class="form-group">
								<label class="control-label col-md-2" for="foreign">Foreign:</label>
								<div class="col-md-10">
									<div class="input-group">
										<input type="text" class="form-control" id="foreign">
										<div class="input-group-btn">
											<button type="button" class="btn btn-default" tabindex="-1" title="Enter IPA symbols" onclick="toggleIpa()">
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
								<textarea class="form-control" rows="3" id="comment" style="resize:vertical" onkeypress="checkEnter(event)"></textarea>
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
							<div class="panel-body"><?php echo getAnnotations(); ?></div>
						</div>
					</div>
					<p>Tip: For fast entry, press tab to move between textboxes and enter to add/save the current entry.</p>
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
		
		<!--- IPA input -->
		<div class="hidden" id="ipa-spacer">
			<div id="ipa">
				<!--<ul class="nav nav-pills">
					<li class="active"><a href="#consonants">Pulmonic consonants</a></li>
					<li><a href="#vowels">Vowels</a></li>
					<li><a href="javascript:void(0)">Non-pulmonic consonants</a></li>
					<li><a href="javascript:void(0)">Other</a></li>
				</ul>-->
				<a name="consonants"></a>
				<table id="consonants">
					<?php
						$ipa = implode("", readConfigFile("IPA"));
						$cols = ["Bilabial", "Labio-<br>dental", "Dental", "Alveolar", "Post-<br>alveolar", "Retro-<br>flex", "Palatal", "Velar", "Uvular", "Pharyn-<br>geal", "Glottal"];
						$rows = [
							"Plosive" => ["p", "b", "", "", "", "", "t", "d", "", "", "ʈ", "ɖ", "c", "ɟ", "k", "ɡ", "q", "ɢ", "", "_", "ʔ", "_"],
							"Nasal" => ["", "m", "", "ɱ", "", "", "", "n", "", "", "", "ɳ", "", "ɲ", "", "ŋ", "", "ɴ", "_", "_", "_", "_"],
							"Trill" => ["", "ʙ", "", "", "", "", "", "r", "", "", "", "", "", "", "_", "_", "", "ʀ", "", "", "_", "_"],
							"Tap or Flap" => ["", "", "", "ⱱ", "", "", "", "ɾ", "", "", "", "ɽ", "", "", "_", "_", "", "", "", "", "_", "_"],
							"Fricative" => ["ɸ", "β", "f", "v", "θ", "ð", "s", "z", "ʃ", "ʒ", "ʂ", "ʐ", "ç", "ʝ", "x", "ɣ", "χ", "ʁ", "ħ", "ʕ", "h", "ɦ"],
							"Lateral fricative" => ["_", "_", "_", "_", "", "", "ɬ", "ɮ", "", "", "", "", "", "", "", "", "", "", "_", "_", "_", "_"],
							"Approximant" => ["", "", "", "ʋ", "", "", "", "ɹ", "", "", "", "ɻ", "", "j", "", "ɰ", "", "", "", "", "_", "_"],
							"Lateral approx." => ["_", "_", "_", "_", "", "", "", "l", "", "", "", "ɭ", "", "ʎ", "", "ʟ", "", "", "_", "_", "_", "_"],
						];
						echo "<tr><th></th>";
						foreach ($cols as $col) echo "<th colspan=2>$col</th>";
						echo "</tr>";
						foreach ($rows as $row => $chars) {
							echo "<tr><th>$row</th>";
							foreach ($chars as $char) {
								if ($char == "_") echo '<td class="gray"></td>';
								elseif ($char == "" || strpos($ipa, $char) === false) echo "<td></td>";
								else echo '<td class="typable" onclick="type(this)">'.$char."</td>";
							}
						}
					?>
				</table>
				<a name="vowels"></a>
				<div id="vowels">
					<?php
						$vowelRows = [
							4 => [1 => "i", 10 => "y", 41 => "ɨ", 50 => "ʉ", 81 => "ɯ", 90 => "u"],
							18 => [23 => "ɪ", 30 => "ʏ", 71 => "ʊ"],
							32 => [14 => "e", 24 => "ø", 48 => "ɘ", 57 => "ɵ", 81 => "ɤ", 90 => "o"],
							45 => [55 => "ə"],
							59 => [27 => "ɛ", 37 => "œ", 54 => "ɜ", 64 => "ɞ", 81 => "ʌ", 90 => "ɔ"],
							73 => [34 => "æ", 62 => "ɐ"],
							87 => [41 => "a", 51 => "ɶ", 81 => "ɑ", 90 => "ɒ"],
						];
						foreach ($vowelRows as $y => $vowelRow) {
							foreach ($vowelRow as $x => $vowel) {
								if (strpos($ipa, $vowel) !== false)
									echo '<div class="vowel typable" style="top:'.$y.'%;left:'.$x.'%;" onclick="type(this)">'.$vowel."</div>";
							}
						}
					?>
				</div>
			</div>
		</div>
		
		<!-- Modal for error -->
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
		
		<!-- Hidden form used to submit data -->
		<form method="POST" action="submit.php" style="display:none">
			<input type="hidden" name="data" id="submission-data">
		</form>
	</body>
</html>