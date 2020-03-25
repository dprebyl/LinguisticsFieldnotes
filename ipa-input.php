<?php
	$orthography = readConfigFile("Orthography");
	$languageName = readConfigFile("LanguageName")[0];
		
	// Used to create the clickable symbols in all charts besides vowels
	function typableTd($symbol, $name = "", $baseNeeded = false) {
		if ($symbol == "") return "<td colspan=2></td>"; // Filler for empty cell on chart
		return '<td class="typable" onclick="type(\'' . $symbol . '\')">'
			. ($baseNeeded ? "◌" : "") . $symbol . "</td>"
			. ($name != "" ? "<td>$name</td>" : "");
	}
	
	// Checks to see if $symbol is in $orthography. Returns it's orthography symbol if so, and false if not.
	function orthographySymbol($symbol) {
		global $orthography;
		foreach ($orthography as $orthEntry) {
			$orthEntry = explode("=", $orthEntry);
			if (count($orthEntry) != 2) continue; // Skip invalid $orthEntry
			if ($orthEntry[0] == $symbol) return $orthEntry[1];
		}
		return false; // Symbol not in $orthography
	}
	
	// === Data to generate IPA input tabs ====================================
	
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
	
	// y% => [x% => "symbol", ...]
	$vowelRows = [
		4 => [1 => "i", 10 => "y", 41 => "ɨ", 50 => "ʉ", 81 => "ɯ", 90 => "u"],
		18 => [23 => "ɪ", 30 => "ʏ", 71 => "ʊ"],
		32 => [14 => "e", 24 => "ø", 48 => "ɘ", 57 => "ɵ", 81 => "ɤ", 90 => "o"],
		45 => [55 => "ə"],
		59 => [27 => "ɛ", 37 => "œ", 54 => "ɜ", 64 => "ɞ", 81 => "ʌ", 90 => "ɔ"],
		73 => [34 => "æ", 62 => "ɐ"],
		87 => [41 => "a", 51 => "ɶ", 81 => "ɑ", 90 => "ɒ"],
	];
	
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
	
	// [symbol, name]
	$otherConsonants = [
		["ʘ", "Bilabial"],			["ɓ", "Bilabial"],
		["ǀ", "Dental"],			["ɗ", "Dental/alveolar"],
		["ǃ", "(Post)alveolar"],	["ʄ", "Palatal"],
		["ǂ", "Palatoalveolar"],	["ɠ", "Velar"],
		["ǁ", "Alveolar lateral"],	["ʛ", "Uvular"],
	];
	
	// [diacritic symbol (optional), non-diacritic symbol, name]
	$tones = [
		["̋", "˥", "Extra high"],	["̌", "˩˥", "Rising"],
		["́", "˦", "High"],			["̂", "˥˩", "Falling"],
		["̄", "˧", "Mid"],			["᷄", "˦˥", "High rising"],
		["̀", "˨", "Low"],			["᷅", "˩˨", "Low rising"],
		["̏", "˩", "Extra low"],		["᷈", "˧˦˧", "Rising-falling"],
		[null, "↓", "Downstep"],	[null, "↗", "Global rise"],
		[null, "↑", "Upstep"],		[null, "↘", "Global fall"],
	];
	
	// [symbol, name]
	$otherSymbols = [
		["ʍ", "Voiceless labial-velar fricative"],	["ɕ", "Voiceless alveolo-palatal fricative"],
		["w", "Voiced labial-velar approximant"],	["ʑ", "Voiced alveol-palatal fricative"],
		["ɥ", "Voiced labial-palatal approximant"],	["ɺ", "Alveolar lateral flap"],
		["ʜ", "Voiceless epiglottal fricative"],	["ɧ", "Simultaneous ʃ and x"],
		["ʢ", "Voiced epiglottal fricative"],		["", ""],
		["ʡ", "Epiglottal plosive"],				["", ""],
	];
?>

<style type="text/css">
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
	#ipa table, .vowels {
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
	#ipa table td {
		height: 22px;
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
	.vowels {
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
</style>

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
				<table>
					<thead><?php
						echo "<tr><th></th>";
						foreach ($consonantCols as $col) echo "<th colspan=2>$col</th>";
						echo "</tr></thead><tbody>";
						foreach ($consonantRows as $row => $chars) {
							echo "<tr><th>$row</th>";
							foreach ($chars as $char) {
								if ($char == "_") echo '<td class="gray"></td>';
								elseif ($char == "") echo "<td></td>"; // Needed because typableTd would create a td with colspan=2
								else {
									$orthSymbol = orthographySymbol($char);
									if ($orthSymbol !== false) echo typableTd($orthSymbol, "", false);
									else echo "<td></td>"; // Charcter not in orthography
								}
							}
						}
					?></tbody>
				</table>
				<div class="vowels">
					<?php
						foreach ($vowelRows as $y => $vowelRow) {
							foreach ($vowelRow as $x => $vowel) {
								$orthSymbol = orthographySymbol($vowel);
								if ($orthSymbol !== false) 
									echo '<div class="vowel typable" style="top:'.$y.'%;left:'.$x.'%;" onclick="type(\''.$orthSymbol.'\')">'.$orthSymbol."</div>";
								else echo "<td></td>"; // Charcter not in orthography
							}
						}
					?>
				</div>
				<table>
					<thead>
						<tr><th colspan=2>Other</th></tr>
					</thead>
					<tbody>
						<?php
							foreach ($orthography as $orthEntry) {
								$orthEntry = explode("=", $orthEntry);
								if (count($orthEntry) >= 3 && $orthEntry[0] == "_") // If it's an "other" symbol
									echo "<tr>" . typableTd($orthEntry[1], $orthEntry[2], false) . "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
			<div id="consonants-vowels" class="tab-pane<?php if (count($orthography) == 0) echo " in active"; ?>">
				<table>
					<thead><?php
						echo "<tr><th></th>";
						foreach ($consonantCols as $col) echo "<th colspan=2>$col</th>";
						echo "</tr></thead><tbody>";
						foreach ($consonantRows as $row => $chars) {
							echo "<tr><th>$row</th>";
							foreach ($chars as $char) {
								if ($char == "_") echo '<td class="gray"></td>';
								elseif ($char == "") echo "<td></td>"; // Needed because typableTd would create a td with colspan=2
								else echo typableTd($char, "", false);
							}
						}
					?></tbody>
				</table>
				<div class="vowels">
					<?php
						foreach ($vowelRows as $y => $vowelRow) {
							foreach ($vowelRow as $x => $vowel) {
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
							foreach ($suprasegmentals as $char) {
								echo "<tr>" . typableTd($char[0], $char[1], $char[2]) . "</tr>";
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
							foreach ($otherConsonants as $i => $char) {
								if ($i%2 == 0) echo "<tr>"; 
								echo typableTd($char[0], $char[1], false);
								if ($i%2 == 1) echo "</tr>";
							}
						?>
						<tr><td colspan=4 style="border:none">&nbsp;</td></tr>
						<tr><td class="typable" onclick="type(''')">'</td><th style="text-align:center" colspan=3>Ejective</th></tr>
					</tbody>
				</table>
				<table>
					<thead>
						<tr><th colspan=6>Tones and Word Accents</th></tr>
						<tr><th colspan=3>Level</th><th colspan=3>Contour</th></tr>
					</thead>
					<tbody>
						<?php
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