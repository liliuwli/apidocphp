<?php
	namespace apidoc\Worker;
	class apipermission{
		protected $_messages = array(
			'common' => array(
				'element'	=>'apiPermission',
				'usage'		=>'@apiPermission group',
				'example'	=>'@apiDefine MyValidPermissionGroup Some title\n@apiPermission MyValidPermissionGroup',
			)
		);
		
		public $parsedFiles = array();
		
		public function preProcess($parsedFiles,$filenames,$packageInfos,$target='definePermission'){
			$source = 'define';
			$result = array();
			$result[$target] = array();
			
			foreach($parsedFiles as $parsedFile){
				foreach($parsedFile as $block){
					if(isset($block['global'][$source]) && !empty($block['global'][$source])){
						$name = $block['global'][$source]['name'];
						$version = isset($block['local']['version'])&&!empty($block['local']['version'])?$block['local']['version']:'0.0.0';
						
						if(!isset($result[$target][$name]) || empty($result[$target][$name]))
							$result[$target][$name] = array();
						
						// fetch from local
						$result[$target][$name][$version] = $block['global'][$source];
					}
				}
			}
			
			if(count($result[$target]) === 0)
				unset($result[$target]);
			
			return $result;
		}
		
		public function postProcess($parsedFiles,$filenames,$preProcess,$packageInfos,$source='definePermission',$target='permission',$messages=''){
			if(empty($messages))
				$messages = $this->_messages;
			
			$this->parsedFiles = $parsedFiles;
			foreach($parsedFiles as $parsedFileIndex=>$parsedFile){
				
				foreach($parsedFile as $k=>$block){
					if(!isset($block['local'][$target]) || empty($block['local'][$target]))
						continue ;
					$newpromiss = array();
					
					foreach($block['local'][$target] as $definition){
						$name = $definition['name'];
						$version = isset($block['local']['version'])&&!empty($block['local']['version'])?$block['local']['version']:'0.0.0';
						$matchedData = array();
						
						if((!isset($preProcess[$source]) || empty($preProcess[$source])) || (!isset($preProcess[$source][$name]) || empty($preProcess[$source][$name]))){
							$matchedData['name'] = $name;
							$matchedData['title'] = isset($definition['title'])?$definition['title']:'';
							$matchedData['description'] = isset($definition['description'])?$definition['title']:'';
						}else{
							if(isset($preProcess[$source][$name][$version])&&!empty($preProcess[$source][$name][$version])){
								$matchedData = $preProcess[$source][$name][$version];
							}else{
								$foundIndex = -1;
								$lastVersion = '0.0.0';
								$versionKeys = array_keys($preProcess[$source][$name]);
								//修改
								foreach($versionKeys as $versionIndex=>$currentVersion){
									if($this->gte($version,$currentVersion) && $this->gte($currentVersion,$lastVersion)){
										$lastVersion = $currentVersion;
										$foundIndex = $versionIndex;
									}
								}
								
								if($foundIndex === -1)
									throw new Exception('Referenced definition has no matching or a higher version. Check version number in referenced define block.file:'.$filenames[$parsedFileIndex].'block:'.$block['index'].'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example'].'groupname:'.$name);
								
								$versionName = $versionKeys[$foundIndex];
								$matchedData = $preProcess[$source][$name][$versionName];
							}
						}
						$newpromiss[] = $matchedData;
					}
					$block['local'][$target] = $newpromiss;
					$this->parsedFiles[$parsedFileIndex][$k] = $block;
				}
				
			}
			
		}
		
		private function gte($v1,$v2){
			$arr1 = explode('.',$v1);
			$arr2 = explode('.',$v2);
			
			foreach($arr1 as $k=>$v){
				if($v>$arr2[$k]){
					return true;
				}elseif($v<$arr2[$k]){
					return false;
				}else{
					continue;
				}
			}
			return true;
		}
	}