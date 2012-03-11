<?php

	require_once(TOOLKIT . '/class.datasource.php');
	require_once(EXTENSIONS . '/gettext/lib/class.gettext.php');
	
	Class datasourcegettext extends Datasource {
	
	    public $dsParamROOTELEMENT = 'resources';
	
	    public function about(){
	        return array(
	                 'name' => 'gettext resources',
	                 'author' => array(
	                        'name' => 'Remie Bolte',
	                        'email' => 'r.bolte@gmail.com'),
	                );
	    }
	
	    public function grab(&$param_pool){
	        $result = new XMLElement($this->dsParamROOTELEMENT);
	        $result->setAttribute('type', gettext::getParserType());
	        
			$parser = gettext::getParser();
			$resources = $parser->parse();
			
			if($parser instanceof POParser) {

				foreach($resources as $resource) {
					$resourceNode = new XMLElement('resource');
					$resourceNode->setAttribute('regionCode', $resource['rc']);
					$resourceNode->setAttribute('languageCode', $resource['lc']);
					$resourceNode->setAttribute('countryCode', $resource['cc']);
					
					$content = $resource['content'];
					foreach($content as $name=>$context) {
						$contextNode = new XMLElement('context');
						if($name != '') { $contextNode->setAttribute('name',$name); }
						
						foreach($context as $item) {
							$itemNode = new XMLElement('item');
							$itemNode->appendChild(new XMLElement('msgid',$item['msgid']));

							if(isset($item['msgid_plural'])) {
								$itemNode->appendChild(new XMLElement('msgid_plural',$item['msgid_plural']));
							}
							
							if(is_array($item['msgstr'])) {
								for($i=0;$i<count($item['msgstr']);$i++) {
									$node = new XMLElement('msgstr', $item['msgstr'][$i]);
									$node->setAttribute('index',$i);
									$itemNode->appendChild($node);
								}
							} else {
								$itemNode->appendChild(new XMLElement('msgstr',$item['msgstr']));
							}
							$contextNode->appendChild($itemNode);
						}
						
						$resourceNode->appendChild($contextNode);
					}
					
					$result->appendChild($resourceNode);
					
					
				}				

				/*
				$localizedResources = $resources[$languagCode];
				$content = $localizedResources['content'][''];
	
				foreach($content as $item) {
					$name = gettext::createValidParameterName($item['msgid']);
					$value = $item['msgstr'];
					$context["params"][$name] = $value;
				}
				*/
			
			} else if($parser instanceof i18nParser) {

				foreach($resources as $resource) {
					$resourceNode = new XMLElement('resource');
					$resourceNode->setAttribute('regionCode', $resource['rc']);
					$resourceNode->setAttribute('languageCode', $resource['lc']);
					$resourceNode->setAttribute('countryCode', $resource['cc']);
					
					$content = $resource['content'];
					foreach($content as $name=>$value) {
						
						if(empty($value)) {
							if(isset($resources['']['content'][$name])) {
								$value = $resources['']['content'][$name];
							}
							$value = (empty($value)) ? $name : $value;
						}
						
						$itemNode = new XMLElement('item',$value);
						$itemNode->setAttribute('name',$name);
						$resourceNode->appendChild($itemNode);
					}
					
					$result->appendChild($resourceNode);					
				}
								
			}
	        
	        return $result;
	    }
	}

?>