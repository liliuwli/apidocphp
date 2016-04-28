<?php
	namespace apidoc\Worker;
	class apisamplerequest{
		public $parsedFiles = array();
		
		public function postProcess($parsedFiles,$filenames,$preProcess,$packageInfos){
			$targetName = 'sampleRequest';
			
			$this->parsedFiles = $parsedFiles;
			
			foreach($parsedFiles as $parsedFileIndex=>$parsedFile){
				foreach($parsedFile as $k=>$block){
					if(isset($block['local'][$targetName])&&!empty($block['local'][$targetName])){
						
						$newBlock  = array();
						foreach($block['local'][$targetName] as $entry){
							if($entry['url'] !== 'off'){
								if($packageInfos['sampleUrl'] && count($entry['url'])>=4 && strtolower(substr($entry['url'],0,4)) !== 'http'){
									$entry['url'] = $packageInfos['sampleUrl'].$entry['url'];
								}
								$newBlock[] = $entry;
							}
						}
						
						if(count($newBlock)===0)
							unset($block['local'][$targetName]);
						else
							$block['local'][$targetName] = $newBlock;
					}else{
						if($packageInfos['sampleUrl'] && $block['local'] && (isset($block['local']['url'])&&$block['local']['url'])){
							$block['local'][$targetName] = array(
								array(
									'url'=>$packageInfos['sampleUrl'].$block['local']['url'],
								),
							);
						}
					}
					$this->parsedFiles[$parsedFileIndex][$k] = $block;
				}
				
			}
		}
	}