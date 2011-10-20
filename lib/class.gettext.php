<?php

	define('GETTEXT_ROOT', MANIFEST . '/resources/');

	require_once(EXTENSIONS . '/gettext/lib/class.poParser.php');
	require_once(EXTENSIONS . '/gettext/lib/class.i18nParser.php');
	
	interface Parser {
		public function parse();
	}
	
	class gettext {
		const PARSER_TYPE_PO = 'po';
		const PARSER_TYPE_I18N = 'i18n';
		
		
		/**
		 * Constant that specifies the default parser type
		 * @var String
		 */
		const DEFAULT_PARSER_TYPE = 'po';
		
		
		public static function getRegionCode() {
			$lc = $_REQUEST['language'];
			$cc = $_REQUEST['region'];

			if($lc == '' && $cc == '') { $regionCode = ''; }
			else if($lc != '' && $cc == '') { $regionCode = $lc . '-' . $lc; }
			else if($lc != '' && $cc != '') { $regionCode = $lc . '-' . $cc; }
			else if($lc == '' && $cc != '') { $regionCode = $cc; }
			
			return $regionCode;
		}
		
		
		/**
		 * Returns the Parser Type ('po' or 'i18n')
		 * Either taken from configuration or, if non specified, the DEFAULT_PARSER_TYPE constant
		 */
		public static function getParserType() {
			$type = Symphony::Configuration()->get('parser','gettext');
			return (($type == '') ? self::DEFAULT_PARSER_TYPE : $type);
		}

		
		/**
		 * Returns the value of the 'params' configuration setting
		 * Default value is False
		 */
		public static function addParameters() {
			$value = Symphony::Configuration()->get('params','gettext');
			if($value == 'true') { return true; }
			return false;
		}
		
		
		/**
		 * Returns a Parser object (POParser or i18nParser)
		 * Parsers will need to support the 'parse()' method
		 * @param String $type Parser type ('po' or 'i18n'). If non specified, the getParserType() method will be called.
		 * @return Parser
		 */
		public static function getParser($type=null) {
			if($type == null) { $type = self::getParserType(); }
			
	    	switch($type) {
	    		case 'i18n': return new i18nParser(); break;
	    		default: return new POParser(); break;
	    	}
		}

		
		/**
		 * Returns all files associated with the specified parser type
		 * @param String $type Parser type ('po' or 'i18n'). If non specified, the getParserType() method will be called.
		 */
		public static function getResourceFiles($type=null) {
			if($type == null) { $type = self::getParserType(); }
			
			$files = array();
			if(file_exists(GETTEXT_ROOT)) {
				if($handle = opendir(GETTEXT_ROOT)) {
				    while (false !== ($file = readdir($handle))) {
				    	switch($type) {
				    		case 'i18n': $suffix = '.properties'; break;
				    		default: $suffix = '.po'; break;
				    	}
				    	
						if (preg_match('/' . $suffix . '$/', strtolower($file))) {
							$files[] = $file;
						}
				    }
				    closedir($handle);
				}
				sort($files);
			}
			return $files;
		}
		
		
		public static function createValidParameterName($name) {
			$name = str_replace(' ','_',$name);
			$name = str_replace('.','_',$name);
			$name = str_replace('%','_',$name);
			$name = str_replace('\r','_',$name);
			$name = str_replace('\n','_',$name);
			$name = htmlspecialchars($name);
			return $name;
		}
		
		
	}
?>