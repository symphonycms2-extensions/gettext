<?php

	Class extension_staticresources extends Extension {

		public function about() {
			return array(
				'name'			=> 'Static Resources',
				'version'		=> '0.9.0',
				'release-date'	=> '2011-09-05',
				'author'		=> array(
					'name'			=> 'Remie Bolte',
					'email'			=> 'r.bolte@gmail.com',
					'website'		=> 'https://github.com/remie/StaticResources'
				),
				'description'	=> 'Add parameters for multilingual static resources'
			);
		}

		/*-------------------------------------------------------------------------
			Delegate
		-------------------------------------------------------------------------*/

		public function getSubscribedDelegates()
		{
			return array(
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendParamsResolve',
					'callback'	=> 'FrontendParamsResolve'
				),
			);
		}
		
		public function install() {
			if(Symphony::ExtensionManager()->fetchStatus("language_redirect") != EXTENSION_ENABLED) {
				Administration::instance()->Page->pageAlert("You need to install the Languag Redirect extension in order to use Static Resources.");
				return false;
			}
		}
		
		
		/*-------------------------------------------------------------------------
			Delegated functions
		-------------------------------------------------------------------------*/	

		public function FrontendParamsResolve($context) {
			if(file_exists(MANIFEST . '/staticresources.xml')) {
				$languageCode = $this->getLanguageCode();
				
				$xml = @file_get_contents(MANIFEST . '/staticresources.xml');
				$xmlDoc = new DOMDocument();
				$xmlDoc->loadXML($xml);

				$xpath = new DOMXpath($xmlDoc);
				$paramNodes = $xpath->query("/resources/language[@code='" . $languageCode . "']/param");
				
				for($i=0;$i < $paramNodes->length;$i++) {
					$paramNode = $paramNodes->item($i);
					
					//NAME
					if($paramNode->hasAttribute('name')) {
						$name = $paramNode->getAttribute('name');
					} else if ($paramNode->hasChildNodes()) {
						$node = $this->findChildNode($paramNode,'name',false);
						$name = $node->nodeValue;
					}
					
					//VALUE
					if($paramNode->hasAttribute('value')) {
						$value = $paramNode->getAttribute('value');
					} else if ($paramNode->hasChildNodes()) {
						$node = $this->findChildNode($paramNode,'value',false);
						$value = $node->nodeValue;
					}
					
					if(empty($name)) {
						throw new Exception("Could not find required 'name' attribute or 'name' childNode");
					} else if(empty($value)) {
						throw new Exception("Could not find required 'value' attribute or 'value' childNode");
					} else if(isset($context["params"][$name])) {
						throw new Exception("Parameter '" . $name . "' already exists!");
					}
						
					$context["params"][$name] = $value;
				}
			}
		}
		
		private function getLanguageCode() {
			require_once(EXTENSIONS . '/language_redirect/lib/class.languageredirect.php');
			$languageCode = LanguageRedirect::instance()->getLanguageCode();
			if(empty($languageCode)) {
				$supported = LanguageRedirect::instance()->getSupportedLanguageCodes();
				$languageCode = $supported[0];
			}
			return $languageCode;
		}
		
		private function findChildNode($node,$name,$recurse) {
			for($i=0;$i < $node->childNodes->length;$i++) {
				$childNode = $node->childNodes->item($i);
				if($childNode->localName == $name) {
					return $childNode;
				} else if($childNode->hasChildNodes && $recurse) {
					return $findChildNode($childNode,$name,$recurse);
				}
			}
			return null;
		}
	}
?>