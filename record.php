<?php
	require("config.php");
	requireLogin();
	
	// Used to create the clickable symbols in all charts besides vowels
	function typableTd($symbol, $name = "", $baseNeeded = false) {
		if ($symbol == "") return "<td colspan=2></td>"; // Filler for empty cell on chart
		// Add this to if to require symbol to be in IPA.txt (will not handle main consonant chart correctly):  || strpos($ipa, $char) === false
		return '<td class="typable" onclick="type(\'' . $symbol . '\')">'
			. ($baseNeeded ? "◌" : "") . $symbol . "</td>"
			. ($name != "" ? "<td>$name</td>" : "");
	}

	$ipa = implode("", readConfigFile("IPA"));
	$orthography = readConfigFile("Orthography");
	$languageName = readConfigFile("LanguageName")[0];
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
			/* All remaining CSS is related to special character input */
			#ipa-spacer {
				height: 270px;
			}
			#ipa {
				position: fixed;
				left: 0;
				bottom: 0;
				width: 100%;
				z-index: 1000;
				white-space: nowrap;
				overflow-x: auto;
				text-align: center;
				line-height: 16px;
				pointer-events: none;
			}
			#ipa .nav-tabs li, #ipa .tab-content {
				pointer-events: auto;
			}
			#ipa .nav-tabs li:first-child { /* Spacing before first tab */
				margin-left: 10px;
			}
			#ipa .nav-tabs > li:not(.active) > a {
				background: #ddd;
			}
			#ipa .tab-pane {
				background: white;
				height: 235px;
				overflow-y: hidden;
			}
			#ipa .tab-pane#orthography {
				height: 44px;
			}
			#ipa table, #vowels {
				display: inline-block;
				vertical-align: top;
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
			#ipa table thead th {
				line-height: 16px;
				text-align: center;
			}
			#ipa table td.typable {
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
			div.typable {
				border: 1px solid black;
				border-radius: 2px;
				font-size: 20px;
				line-height: 20px;
				width: 20px;
				text-align: center;
			}
			.typable.vowel {
				position: absolute;
				background: white;
			}
			.typable.orthography {
				font-size: 32px;
				line-height: 32px;
				width: 32px;
				display: inline-block;
				margin: 4px;
			}
		</style>
		<script type="text/javascript">
			const GLOBAL_INPUTS = ["date", "names", "topic"]; // Apply to the whole session
			const ENTRY_INPUTS = ["foreign", "english", "comment"]; // Apply to each specific entry
			var entries = [];
			var editNum = -1;
			
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
				for (var i = 0; i < GLOBAL_INPUTS.length; i++) {
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
		</script>
	</head>
	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7 top-spacing">
					<form class="form-horizontal" onsubmit="enter()" action="javascript:void(0)">
						<input type="submit" class="hidden"><!-- Required for enter to submit form -->
						<div class="form-group">
							<label class="control-label col-md-2" for="date">Date:</label>
							<div class="col-md-10">
								<input type="date" class="form-control" id="date" oninput="storeGlobal(this)">
								<!-- placeholder="Date of recording" onfocus="this.type='date'" onblur="if(this.value=='')this.type='text'" -->
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
							<div class="form-group" id="add-buttons">
								<div class="col-md-3 col-sm-2 col-xs-3 pull-right">
									<button type="button" class="btn btn-success col-xs-12" onclick="createEntry()">Add</button>
								</div>
							</div>
							<div class="form-group hidden" id="edit-buttons">
								<div class="col-md-3 col-sm-2 col-xs-3">
									<button type="button" class="btn btn-danger col-xs-12" onclick="deleteEntry()">Delete</button>
								</div>
								<div class="col-md-3 col-sm-2 col-xs-3 pull-right">
									<button type="button" class="btn btn-success col-xs-12" onclick="saveEdit()">Save</button>
								</div>
							</div>
						</div>
					</form>
					<div class="panel-group">
						<div class="panel panel-info">
							<div class="panel-heading">Annotations</div>
							<div class="panel-body"><?=getAnnotations()?></div>
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
		<div class="hidden" id="ipa-spacer">
			<div id="ipa">
				<ul class="nav nav-tabs">
					<?php if (count($orthography) > 0): ?>
						<li class="active"><a data-toggle="tab" href="#orthography"><?=$languageName?> Orthography</a></li>
						<li>
					<?php else: ?>
						<li class="active">
					<?php endif; ?>
					<a data-toggle="tab" href="#consonants-vowels">Consonants/Vowels</a></li>
					<li><a data-toggle="tab" href="#diacritics">Diacritics</a></li>
					<li><a data-toggle="tab" href="#other">Tones/Accents/Other</a></li>
				</ul>
				<div class="tab-content">
					<div id="orthography" class="tab-pane<?php if (count($orthography) > 0) echo " in active"; ?>">
						<?php
							foreach ($orthography as $char) {
								echo '<div class="typable orthography" onclick="type(\''.$char.'\')">'.$char."</div>";
							}
						?>
					</div>
					<div id="consonants-vowels" class="tab-pane<?php if (count($orthography) == 0) echo " in active"; ?>">
						<table id="consonants">
							<thead><?php
								
								$consonantCols = ["Bilabial", "Labio-<br>dental", "Dental", "Alveolar", "Post-<br>alveolar", "Retro-<br>flex", "Palatal", "Velar", "Uvular", "Pharyn-<br>geal", "Glottal"];
								$consonantRows = [
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
								foreach ($consonantCols as $col) echo "<th colspan=2>$col</th>";
								echo "</tr></thead><tbody>";
								foreach ($consonantRows as $row => $chars) {
									echo "<tr><th>$row</th>";
									foreach ($chars as $char) {
										if ($char == "_") echo '<td class="gray"></td>';
										elseif ($char == "" || strpos($ipa, $char) === false) echo "<td></td>";
										else echo typableTd($char, "", false);
									}
								}
							?></tbody>
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
											echo '<div class="vowel typable" style="top:'.$y.'%;left:'.$x.'%;" onclick="type(\''.$vowel.'\')">'.$vowel."</div>";
									}
								}
							?>
						</div>
					</div>
					<div id="diacritics" class="tab-pane">
						<table>
							<thead>
								<tr><th colspan=8>Diacritics</th></tr>
							</thead>
							<tbody>
								<?php
									
									
									// [symbol, name, if base needed]
									$diacritics = [
										["̥", "Voiceless", true],		["̩", "Syllabic", true],			["ˤ", "Pharyngealized", false],				["̃", "Nasalized", true],		
										["̬", "Voiced", true],			["̯", "Non-syllabic", true],		["̴", "Velarized or pharyngealized", true],	["ⁿ", "Nasal release", false],
										["ʰ", "Aspirated", false],		["˞", "Rhoticity", false],		["̝", "Raised", true], 						["ˡ", "Lateral release", false],
										["̹", "More rounded", true],		["̤", "Breathy voiced", true],	["̞", "Lowered", true],						["̚", "No audible release", true],
										["̜", "Less rounded", true],		["̰", "Creaky voiced", true],	["̘", "Advanced Tongue Root", true], 		["", "", false],
										["̟", "Advanced", true],			["̼", "Linguolabial", true],		["̙", "Retracted Tongue Root", true],		["", "", false],
										["̠", "Retracted", true],		["ʷ", "Labalized", false],		["̪", "Dental", true],						["", "", false],
										["̈", "Centralized", true],		["ʲ", "Palatalized", false],	["̺", "Apical", true],						["͡", "Tie bar (above)", true],
										["̽", "Mid-centralized", true],	["ˠ", "Velarized", false],		["̻", "Laminal", true],						["͜", "Tie bar (below)", true],
									];
									
									foreach ($diacritics as $i => $char) {
										if ($i%4 == 0) echo "<tr>";
										echo typableTd($char[0], $char[1], $char[2]);
										if ($i%4 == 3) echo "</tr>";
									}
								?>
							</tbody>
						</table>
						<table>
							<thead>
								<tr><th colspan=2>Suprasegmentals</th></tr>
							</thead>
							<tbody>
								<?php
									// [symbol, name, if base is needed]
									$suprasegmentals = [
										["ˈ", "Primary stress", false],
										["ˌ", "Secondary stress", false],
										["ː", "Long", false],
										["ˑ", "Half-long", false],
										["̆", "Extra short", true],
										["|", "Minor (foot) group", false],
										["‖", "Major (intonation) group", false],
										[".", "Syllable break", false],
										["‿", "Linking (absence of a break)", false],
									];
									foreach ($suprasegmentals as $char) {
										echo "<tr>";
										echo typableTd($char[0], $char[1], $char[2]);
										echo "</tr>";
									}
								?>
							</tbody>
						</table>
					</div>
					<div id="other" class="tab-pane">
						<table>
							<thead>
								<tr><th colspan=4>Non-Pulmonic Consonants</th></tr>
								<tr><th colspan=2>Clicks</th><th colspan=2>Voiced Implosives</th></tr>
							</thead>
							<tbody>
								<?php
									// [symbol, name]
									$otherConsonants = [
										["ʘ", "Bilabial"], ["ɓ", "Bilabial"],
										["ǀ", "Dental"], ["ɗ", "Dental/alveolar"],
										["ǃ", "(Post)alveolar"], ["ʄ", "Palatal"],
										["ǂ", "Palatoalveolar"], ["ɠ", "Velar"],
										["ǁ", "Alveolar lateral"], ["ʛ", "Uvular"],
									];
									foreach ($otherConsonants as $i => $char) {
										if ($i%2 == 0) echo "<tr>"; 
										echo typableTd($char[0], $char[1], false);
										if ($i%2 == 1) echo "</tr>";
									}
								?>
								<tr><td colspan=4 style="border:none">&nbsp;</td></tr>
								<tr><td class="typable" onclick="type('ʼ')">ʼ</td><th style="text-align:center" colspan=3>Ejective</th></tr>
							</tbody>
						</table>
						<table>
							<thead>
								<tr><th colspan=6>Tones and Word Accents</th></tr>
								<tr><th colspan=3>Level</th><th colspan=3>Contour</th></tr>
							</thead>
							<tbody>
								<?php
									// [diacritic symbol (optional), non-diacritic symbol, name]
									$tones = [
										["̋", "˥", "Extra high"],
										["̌", "˩˥", "Rising"],
										["́", "˦", "High"],
										["̂", "˥˩", "Falling"],
										["̄", "˧", "Mid"],
										["᷄", "˦˥", "High rising"],
										["̀", "˨", "Low"],
										["᷅", "˩˨", "Low rising"],
										["̏", "˩", "Extra low"],
										["᷈", "˧˦˧", "Rising-falling"],
										[null, "↓", "Downstep"],
										[null, "↗", "Global rise"],
										[null, "↑", "Upstep"],
										[null, "↘", "Global fall"],
									];
									foreach ($tones as $i => $char) {
										if ($i%2 == 0) echo "<tr>"; 
										if ($char[0] == null) echo "<td></td>";
										else echo typableTd($char[0], "", true);
										echo typableTd($char[1], $char[2], false);
										if ($i%2 == 1) echo "</tr>";
									}
								?>
							</tbody>
						</table>
						<table>
							<thead>
								<tr><th colspan=4>Other Symbols</th></tr>
							</thead>
							<tbody>
								<?php
									// [symbol, name]
									$otherSymbols = [
										["ʍ", "Voiceless labial-velar fricative"],	["ɕ", "Voiceless alveolo-palatal fricative"],
										["w", "Voiced labial-velar approximant"],	["ʑ", "Voiced alveol-palatal fricative"],
										["ɥ", "Voiced labial-palatal approximant"],	["ɺ", "Alveolar lateral flap"],
										["ʜ", "Voiceless epiglottal fricative"],	["ɧ", "Simultaneous ʃ and x"],
										["ʢ", "Voiced epiglottal fricative"],		["", ""],
										["ʡ", "Epiglottal plosive"],				["", ""],
									];
									foreach ($otherSymbols as $i => $char) {
										if ($i%2 == 0) echo "<tr>"; 
										echo typableTd($char[0], $char[1], false);
										if ($i%2 == 1) echo "</tr>";
									}
								?>
							</tbody>
						</table>
						<br><a href="https://westonruter.github.io/ipa-chart/keyboard/" target="_blank">Check here if the symbol you need is missing</a>
					</div>
				</div>
			</div>
		</div>
		
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
