<?php

	require_once(EXTENSIONS . '/gettext/lib/class.gettext.php');

	/**
	 * Parser for GNU PO translation files
	 * The parser will retrieve all available PO files and return an associative array
	 * containing the resources for each available language.
	 * 
	 * The associative array keys are based on language code and country code
	 * These are retrieved from the file name (see naming convention below).
	 * If no country code is available, the language code will be repeated
	 * 
	 * Example:	Language-code is 'nl' and Country-Code is 'NL'.
	 * 			The Array key will be 'nl-NL'
	 * Example:	Language-code is 'de' and Country-Code is empty.
	 * 			The Array key will be 'de-de'
	 * Example:	Language-code is empty and Country-Code is 'uk'.
	 * 			The Array key will be 'uk'
	 * 
	 * PO files naming convention
	 * It is imperitive that the file name convention is strictly enforced
	 * This is due to the fact that the file name includes information on
	 * language-code, country-code and file encoding that is required for
	 * parsing the file
	 * 
	 * Default language file:
	 * 		'name.[encoding].po'
	 * 
	 * 		encoding: file encoding (default is UTF-8)
	 * 
	 * Region specific language file:
	 * 		'name.[lc]_[cc].[encoding].po'
	 * 
	 * 		lc: language code
	 * 		cc: country code
	 * 		encoding: file encoding (default is UTF8)
	 */
	class POParser implements Parser {
		
		/**
		 * The FILE_PATTERN regular expression will match following naming convention of PO files:
		 * 		'name.[lc]_[cc].[enc].po'
		 * 
		 * Name and extension are required, language-code (lc), country-code (cc) and encoding (enc) are optional.
		 * When used with preg_match() it will always return an array with 6 entries:
		 * Array(
		 * 		[0] Full name
		 * 		[1]	file name
		 * 		[2] language code
		 * 		[3]	country code
		 * 		[4]	encoding
		 * 		[5]	extension
		 * )
		 * Some of these entries will remain empty if no value has been specified.
		 * @var String
		 */
		const FILE_PATTERN='/([A-Za-z0-9]*)[\.]?([A-Za-z0-9]*)[_]?([A-Za-z0-9_]*)[\.]?([A-Za-z0-9_-]*)[\.](.*)/i';

		
		/**
		 * Returns an associative array of all parsed PO files
		 * 
		 * The associative array keys are based on language code and country code
		 * These are retrieved from the file name (see naming convention below).
		 * If no country code is available, the language code will be repeated
		 * 
		 * Example:	Language-code is 'nl' and Country-Code is 'NL'.
		 * 			The Array key will be 'nl-NL'
		 * Example:	Language-code is 'de' and Country-Code is empty.
		 * 			The Array key will be 'de-de'
		 * Example:	Language-code is empty and Country-Code is 'uk'.
		 * 			The Array key will be 'uk'
		 * 
		 * Array (
		 * 		['fullname'] Full name of parsed file
		 * 		['filename'] File name without language or encoding information and no extension
		 * 		['lc'] Language-Code (optional)
		 * 		['cc'] Country-Code (optional)
		 * 		['enc'] File encoding (optional)
		 * 		['ext'] File extension
		 * 		['content'] Array (
		 * 				[context] Array (
		 * 						[0-N] Array (
		 * 								['msgctxt'] Message context, corresponds with context array item (optional)
		 * 								['msgid'] String identifier of translation
		 * 								['msgid_plural'] String identifier for plural tanslation
		 * 								['msgstr'] String with translated message, or Array of 2 items with translated message and plural
		 * 						)
		 * 				)
		 * 		)
		 * )
		 * 
		 */
		public function parse() {
			$resources = array();
			$files = gettext::getResourceFiles('po');
			foreach($files as $file) {
				preg_match_all(self::FILE_PATTERN, $file, $parts);
				
				$resource = array();
				$resource['fullname'] = $parts[0][0];
				$resource['filename'] = $parts[1][0];
				$resource['lc'] = $parts[2][0];
				$resource['cc'] = $parts[3][0];
				$resource['enc'] = $parts[4][0];
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
		 * Actual parser for PO file
		 * Transforms file content to Array
		 * 
		 * @param String $path Path to PO file
		 * @return Array
		 */
		private function getResourcesFromFile($path) {
			$content = @file_get_contents($path);
			$content = str_replace("\r\n", "\n", $content);
			$lines = explode("\n", $content);
			
			$resources = array();
			$blocks = $this->getDefinitionBlocks($lines);
			foreach($blocks as $block) {
				$resource = $this->parseBlockRules($block);
				$context = $resource['msgctxt'];
				$resources[$context][] = $resource;
			}
			
			return $resources;
		}
		
		/**
		 * Retrieve message definition blocks
		 * See http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files
		 * for more information on definition blocks formating
		 * 
		 * @param Array $lines Array of lines in PO file
		 * @return Array
		 */
		private function getDefinitionBlocks($lines) {
			$blocks = array();
			$rules = array();
			
			for($i=0; $i<count($lines); $i++) {
				$line = trim($lines[$i]);
				if($line == '') {
					if(count($rules) > 0) {
						$rules[] = '';
						$blocks[] = $rules;
					}
					$rules = array();	
				} else {
					$rules[] = trim($line);
				}
			}
			return $blocks;
		}
		
		/**
		 * Parse message definition blocks
		 * See http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files
		 * for more information on definition blocks formating
		 * 
		 * @param Array $block Array with lines specific to definition block
		 * @return Array
		 */
		private function parseBlockRules($block) {
			$resource = array();
			foreach($block as $rule) {

				if($isMultiLine) {
					if(substr($rule, 0, 1) == '"') {
						$multiline .= substr($rule, 1, strlen($rule) - 2);
					} else {
						$resource[$multilineType] = $multiline;
						$isMultiLine = false;
						$multiline = '';
					}
				}
				
				if(substr($rule, 0, 8) == 'msgid ""') {
					$isMultiLine = true;
					$multilineType = 'msgid';
					continue;
				} else if(substr($rule, 0, 9) == 'msgstr ""') {
					$isMultiLine = true;
					$multilineType = 'msgstr';
					continue;
				}
								
				if(substr($rule, 0, 7) == 'msgctxt') {
					$resource['msgctxt'] = substr($rule, 8, strlen($rule) - 9);
				} else if(substr($rule, 0, 12) == 'msgid_plural') {
					$resource['msgid_plural'] = substr($rule, 14, strlen($rule) - 15);
				} else if(substr($rule, 0, 5) == 'msgid') {
					$resource['msgid'] = substr($rule, 7, strlen($rule) - 8);
				} else if(substr($rule, 0, 7) == 'msgstr[') {
					if(!isset($resource['msgstr'])) { $resource['msgstr'] = array(); }
					$index = substr($rule, 7, 1);
					$resource['msgstr'][$index] = substr($rule, 11, strlen($rule) - 12);
				} else if(substr($rule, 0, 6) == 'msgstr') {
					$resource['msgstr'] = substr($rule, 8, strlen($rule) - 9);
				}
			}
			return $resource;
		}
	}