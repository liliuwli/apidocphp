<?php
	namespace apidoc\Filter;
	class apiparam{
		public $parsedFiles = array();
		
		public function postFilter($parsedFiles,$filenames,$tagName = 'parameter'){
			$this->parsedFiles = $parsedFiles;
			foreach($parsedFiles as $pindex=>$parsedFile){
				foreach($parsedFile as $bindex=>$block){
					if((isset($block['local'][$tagName]) && $block['local'][$tagName]) && (isset($block['local'][$tagName]['fields'])&&$block['local'][$tagName]['fields'])){
						
						$blockFields = $block['local'][$tagName]['fields'];
						foreach($blockFields as $blockFieldKey=>$fields){
							$newFields = array();
							$existingKeys =  array();
							foreach($fields as $field){
								$key = $field['field'];
								if(!isset($existingKeys[$key])){
									$existingKeys[$key] = 1;
									$newFields[] = $field;
								}
							}
							$block['local'][$tagName]['fields'][$blockFieldKey] = $newFields;
							$this->parsedFiles[$pindex][$bindex] = $block;
						}
					}
				}
			}
		}
	}