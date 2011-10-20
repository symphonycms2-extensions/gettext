<?php

	require_once(EXTENSIONS . '/gettext/lib/class.gettext.php');

	/**
	 * Parser for JAVA Style i18n Resource Bundle property files
	 * The parser will retrieve all available property files and return an associative array
	 * containing the resources for each available language.
	 * 
	 * The associative array keys are based on Region Code (combination of language code and country code)
	 * These are retrieved from the file name (see naming convention below).
	 * If no country code is available, the language code will be repeated
	 * 
	 * Example:	Language-code is 'nl' and Country-Code is 'NL'.
	 * 			The Region Code will be 'nl-NL'
	 * Example:	Language-code is 'de' and Country-Code is empty.
	 * 			The Region Code will be 'de-de'
	 * Example:	Language-code is empty and Country-Code is 'uk'.
	 * 			The Region Code will be 'uk'
	 * 
	 * I18n files naming convention
	 * It is imperitive that the file name convention is strictly enforced
	 * This is due to the fact that the file name includes information on
	 * language-code, country-code and file encoding that is required for
	 * parsing the file
	 * 
	 * Default language file:
	 * 		'name.properties'
	 * 
	 * Region specific language file:
	 * 		'name.[lc]_[cc].properties'
	 * 
	 * 		lc: language code
	 * 		cc: country code
	 */	
	class i18nParser {
		
		/**
		 * The FILE_PATTERN regular expression will match following naming convention of resource bundle files:
		 * 		'name.[lc]_[cc].properties'
		 * 
		 * Name and extension are required, language-code (lc) and country-code (cc) are optional.
		 * When used with preg_match() it will always return an array with 5 entries:
		 * Array(
		 * 		[0] Full name
		 * 		[1]	file name
		 * 		[2] language code
		 * 		[3]	country code
		 * 		[4]	extension
		 * )
		 * Some of these entries will remain empty if no value has been specified.
		 * @var String
		 */
		const FILE_PATTERN = "/([A-Za-z0-9]*)[\.]?([A-Za-z0-9]*)[_]?([A-Za-z0-9_]*)[\.](.*)/";

		
		/**
		 * Returns an associative array of all parsed property files
		 * 
		 * The associative array keys are based on Region Code (combination of language code and country code)
		 * These are retrieved from the file name (see naming convention below).
		 * If no country code is available, the language code will be repeated
		 * 
		 * Example:	Language-code is 'nl' and Country-Code is 'NL'.
		 * 			The Region Code will be 'nl-NL'
		 * Example:	Language-code is 'de' and Country-Code is empty.
		 * 			The Region Code will be 'de-de'
		 * Example:	Language-code is empty and Country-Code is 'uk'.
		 * 			The Region Code will be 'uk'
		 * 
		 * Array (
		 * 		['fullname'] Full name of parsed file
		 * 		['filename'] File name without language or encoding information and no extension
		 * 		['rc'] Region-Code
		 * 		['lc'] Language-Code (optional)
		 * 		['cc'] Country-Code (optional)
		 * 		['enc'] File encoding (optional)
		 * 		['ext'] File extension
		 * 		['content'] Array ($key=>$value)
		 * )
		 * 
		 */
		public function parse() {
			$resources = array();
			$files = gettext::getResourceFiles('i18n');
			foreach($files as $file) {
				preg_match_all(self::FILE_PATTERN, $file, $parts);
				
				$resource = array();
				$resource['fullname'] = $parts[0][0];
				$resource['filename'] = $parts[1][0];
				$resource['lc'] = $parts[2][0];
				$resource['cc'] = $parts[3][0];
				$resource['ext'] = $parts[5][0];
				$resource['content'] = $this->getResourcesFromFile(GETTEXT_ROOT . '/' . $file);
				
				if($resource['lc'] == '' && $resource['cc'] == '') { $regionCode = ''; }
				else if($resource['lc'] != '' && $resource['cc'] == '') { $regionCode = $resource['lc'] . '-' . $resource['lc']; }
				else if($resource['lc'] != '' && $resource['cc'] != '') { $regionCode = $resource['lc'] . '-' . $resource['cc']; }
				else if($resource['lc'] == '' && $resource['cc'] != '') { $regionCode = $resource['cc']; }
				$resource['rc'] = $regionCode;
				
				$resources[$regionCode] = $resource;
			}
			
			return $resources;
		}
		
		
		/**
		 * Actual parser for property file
		 * Transforms file content to Array
		 * 
		 * @param String $path Path to property file
		 * @return Array
		 */
		private function getResourcesFromFile($path) {
			$content = @file_get_contents($path);
			$content = str_replace("\r\n", "\n", $content);
			$lines = explode("\n", $content);
			
			$resources = array();
			for($i=0; $i < count($lines); $i++) {
				$line = trim($lines[$i]);
				if($line=='') { continue; }
				
				$name = trim(substr($line, 0, strpos($line,'=')));
				$value = trim(substr($line, strpos($line,'=') + 1));

				if(substr($line, strlen($line) - 1, 1) == '\\') {
					$multiline = true;
					$lineCount = 1;
					$value = trim(substr($value, 0, strlen($value) - 1));

					while($multiline) {
						$currentLine = $lines[$i + $lineCount];
						if(substr($currentLine, strlen($currentLine) - 1, 1) == '\\') {
							$value .= ' ' . trim(substr($currentLine, 0, strlen($currentLine) - 1));
							$lineCount++;
						} else {
							$value .= ' ' . trim($currentLine);
							$multiline = false;
							break;
						}
					}
					
					$i = $i + $lineCount;
				}

				if($name != '' && !isset($resources[$name])) {
					$resources[$name] = $value;
				}
			}
			
			return $resources;
		}

	}		
?>