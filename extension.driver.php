<?php

	require_once(EXTENSIONS . '/gettext/lib/class.gettext.php');

	Class extension_gettext extends Extension {

		public function install() {
			if(!is_writable(MANIFEST)) {
				Administration::instance()->Page->pageAlert("The 'Manifest' folder is not writable.");
				return false;
			} else if(!file_exists(GETTEXT_ROOT)) {
				mkdir(GETTEXT_ROOT);
			}
			
			Symphony::Configuration()->set('parser', 'po', 'gettext');
			Symphony::Configuration()->set('params', 'true', 'gettext');
			Symphony::Configuration()->set('mergedefault', 'false', 'gettext');
			Administration::instance()->saveConfig();
		}

		public function uninstall() {
			Symphony::Configuration()->remove('gettext');
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
			Administration::instance()->Page->addScriptToHead(URL . '/symphony/assets/jquery.js', 9200000);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/gettext/assets/gettext.js', 9300000);
			
    		$group = new XMLElement('fieldset',null,array('class'=>'settings'));
    		$group->appendChild(new XMLElement('legend', 'Gettext'));
    
    		$div = new XMLElement('div');
    			// PARSER
	    		$label = Widget::Label('Parser');
					$options = array();
					$options[] = array(gettext::PARSER_TYPE_PO, (gettext::getParserType() == gettext::PARSER_TYPE_PO), __('GNU Gettext (PO) '));
					$options[] = array(gettext::PARSER_TYPE_I18N, (gettext::getParserType() == gettext::PARSER_TYPE_I18N), __('i18n Properties'));
					$label->appendChild(Widget::Select('settings[gettext][parser]', $options, array('id' => 'gettext_parser')));
				$div->appendChild($label);
    		$group->appendChild($div);
				
				// OPTIONS
			$container = Widget::Label('Options', null, null, 'gettext_options', (gettext::getParserType() == gettext::PARSER_TYPE_PO) ? array('style'=>'display:none') : null);
	    		$div = new XMLElement('div');
		    		$label = Widget::Label();
		                    $input = Widget::Input('settings[gettext][params]', 'true', 'checkbox');
		                    if(gettext::addParameters()) { $input->setAttribute('checked', 'checked'); }
		                    $label->setValue($input->generate() . ' ' . __('Add resources to parameter pool'));
		                    $div->appendChild($label);
		    		$label = Widget::Label();
		    				$input = Widget::Input('settings[gettext][mergedefault]', 'true', 'checkbox');
		                    if(gettext::addParameters()) { $input->setAttribute('checked', 'checked'); }
		                    $label->setValue($input->generate() . ' ' . __('Use default values if localized key is not available'));
		                    $div->appendChild($label);
				$container->appendChild($div);
		    $group->appendChild($container);
    		    
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
			
			if($parser instanceof i18nParser) {
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
