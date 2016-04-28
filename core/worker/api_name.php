<?php
	namespace apidoc\Worker;
	class apiname{
		public $parsedFiles = array();
		
		public function postProcess($parsedFiles,$filenames='',$preProcess='',$packageInfos='',$source='',$target='name',$messages=''){
			$this->parsedFiles = $parsedFiles;
			foreach($parsedFiles as $parsedFileIndex=>$parsedFile){
				
				foreach($parsedFile as $k=>$block){
					if(count($block['global'])===0){
						$name = $block['local'][$target];
						if(empty($name)){
							$type = $block['local']['type'];
							$url = $block['local']['url'];
							$name = strtoupper($type[0]).strtolower(substr($type,1));
							
							$res = preg_match('/[\w]+/',$url,$match);
							if($res){
								foreach($match as $v){
									$name .= strtoupper($v[0]).strtolower(substr($v,1));
								}
							}
						}
						
						$name = preg_replace('/[^\w\x7f-\xff]/','_',$name);
						
						$block['local'][$target] = $name;
					}
					$this->parsedFiles[$parsedFileIndex][$k] = $block;
				}
				
			}
			
		}
	}