<?php

	require_once(EXTENSIONS . '/gettext/lib/class.gettext.php');

	Class extension_gettext extends Extension {

		public function about() {
			return array(
				'name'			=> 'gettext',
				'version'		=> '2.0.0',
				'release-date'	=> '2011-10-20',
				'author'		=> array(
					'name'			=> 'Remie Bolte',
					'email'			=> 'r.bolte@gmail.com',
					'website'		=> 'https://github.com/remie/gettext'
				),
				'description'	=> 'Brings multilingual resource properties to your XSLT templates'
			);
		}

		public function install() {
			if(is_writable(MANIFEST)) {
				mkdir(MANIFEST . '/resources/');
			} else {
				Administration::instance()->Page->pageAlert("The 'Manifest' folder is not writable.");
				return false;
			}
			
		}
		
		/*-------------------------------------------------------------------------
			Delegate
		-------------------------------------------------------------------------*/

		public function getSubscribedDelegates()
		{
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'savePreferences'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendParamsResolve',
					'callback'	=> 'FrontendParamsResolve'
				)
			);
		}
		
		/*-------------------------------------------------------------------------
			Delegated functions
		-------------------------------------------------------------------------*/	

		public function appendPreferences($context) {
    		$group = new XMLElement('fieldset',null,array('class'=>'settings'));
    		$group->appendChild(new XMLElement('legend', 'Gettext'));
    
    		$div = new XMLElement('div',null,array('class'=>'group'));
    			// PARSER
    			$container = new XMLElement('div');
    				$container->appendChild(new XMLElement('h3', 'Parser',array('style'=>'margin-bottom: 5px;')));
					$options = array();
					$options[] = array(gettext::PARSER_TYPE_PO, (gettext::getParserType() == gettext::PARSER_TYPE_PO), __('GNU Gettext (PO) '));
					$options[] = array(gettext::PARSER_TYPE_I18N, (gettext::getParserType() == gettext::PARSER_TYPE_I18N), __('i18n Properties'));
					$container->appendChild(Widget::Select('settings[gettext][parser]', $options));
				$div->appendChild($container);
					
				// OPTIONS
    			$container = new XMLElement('div');
	    			$container->appendChild(new XMLElement('h3', 'Options',array('style'=>'margin-bottom: 5px;')));
		    		$label = Widget::Label();
		                    $input = Widget::Input('settings[gettext][params]', 'true', 'checkbox');
		                    if(gettext::addParameters()) { $input->setAttribute('checked', 'checked'); }
		                    $label->setValue($input->generate() . ' ' . __('Add resources to parameter pool'));
		                    $container->appendChild($label);
				$div->appendChild($container);
    		$group->appendChild($div);
    
    		// Append preferences
    		$context['wrapper']->appendChild($group);			
		}
		
		public function savePreferences($context) {
			$context['settings']['gettext']['params'] = isset($_REQUEST['settings']['gettext']['params']) ? 'true' : 'false';
		}
		
		public function FrontendParamsResolve($context) {
			if(!gettext::addParameters()) { return; }
			
			$parser = gettext::getParser();
			$languageCode = gettext::getRegionCode();
			$resources = $parser->parse();
			
			if($parser instanceof POParser) {
				// Add translations to Parameter Pool (bad idea!)				
				if(isset($resources[$languageCode])) { $localizedResources = $resources[$languageCode]; }
				else { $localizedResources = $resources['']; }

				if(isset($localizedResources['content'])) {
					$content = $localizedResources['content'][''];
					if(is_array($content)) {
						foreach($content as $item) {
							$name = gettext::createValidParameterName($item['msgid']);
							$value = $item['msgstr'];
							$context["params"][$name] = $value;
						}
					}
				}
			} else if($parser instanceof i18nParser) {
				// Add properties to Parameter Pool
				if(isset($resources[$languageCode])) { $localizedResources = $resources[$languageCode]; }
				else { $localizedResources = $resources['']; }
				
				if(isset($localizedResources['content'])) {
					$content = $localizedResources['content'];
					if(is_array($content)) {
						foreach($content as $name=>$value) {
							$name = gettext::createValidParameterName($name);
							$context["params"][$name] = $value;
						}
					}
				}
			}
		}
	}
?>
