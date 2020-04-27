# Linguistics Fieldnotes
A web application that helps students store fieldnotes from interactions with a language consultant for later parsing

![Example screenshot](/sample-configuration-files/screenshot.png)

## Features
* Enter phrases in the consultant's language, their corresponding English translations, and any comments
* Optionally, listen to audio recordings of the interaction while entering notes and log timestamps
* Store examples from narrative told naturally by consultant, with morpheme-by-morpheme glossing
* Built-in IPA keyboard with diacritics
* Students can easily copy or download contributions after submitting
* Entries in progress saved to local storage in case of computer crashes
* Automatic backups after every submission (optional)

## Setup
1. Requires access to a web server with PHP >= 5.6 (tested primarily with PHP 7)
2. Place all files in the web root, except for the sample configuration files
3. Place `FieldnotesConfig.ini` in the directory above the web root
   * If the web root is `~/public_html`, place this file in `~`
4. Edit `FieldnotesConfig.ini` to specify the directories you want each type of file stored in
5. Place the remaining configuration files and recordings in the directories chosen

## Configuration files
* `Annotations.txt`: The contents of the Annotations box students see while making entries
* `LanguageName.txt`: The name of the consultant's language used in a few places throughout the app, crucially including determining the name of the fieldnotes file.
* `Orthography.txt`: Determines what IPA symbols correspond to what Orthography symbols
* `StudentIDs.txt`: A list of valid logins (could also use usernames, a shared class password, etc.)
Note: Mistakes in configuration files may result in HTTP 500 errors

## Recordings
* Fieldnotes recordings are named in the format `2020Apr1-Description.mp3`
  * Description can be whatever you want (e.g. topics covered, name of the person who made the recording, etc.) or omitted entirely
  * The date format must match above (4 digit year, 3 letter month, and no leading zero on the date) or the recording will not be visible
  * The file format can be mp3, m4a, or wav
* Narrative recordings are named in the format `NARR-Description.mp3`
  * The same rules apply for the description and file format

## Output file format
* Fieldnotes are stored in a file with the format `LanguageFieldnotes-SemesterYear.txt`
  * Example: `TurkmenFieldnotes-Spring2020`
  * The language comes from `LanguageName.txt`
  * The semester is Spring in January-May, Summer in June and July, and Fall in August-December
* Narrative examples are stores in files with the same name as their corresponding narrative recording