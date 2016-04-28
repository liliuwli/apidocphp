<?php
	namespace apidoc\Worker;
	class apierrorstructure{
		//错误提示信息
		protected $_messages = array(
			'common' => array(
				'element'	=>'apiErrorStructure',
				'usage'		=>'@apiErrorStructure group',
				'example'	=>'@apiDefine MyValidErrorStructureGroup Some title\n@apiErrorStructure MyValidErrorStructureGroup',
			)
		);
		
		public $parsedFiles = array();
		
		//预处理		返回全局定义的内容
		public function preProcess($parsedFiles,$filenames,$packageInfos,$target='defineErrorStructure'){
			$source = $target;
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
						$result[$target][$name][$version] = $block['local'];
					}
				}
			}
			
			if(count($result[$target]) === 0)
				unset($result[$target]);
			
			return $result;
		}
		
		//正式处理		定义的内容赋予继承者
		public function postProcess($parsedFiles,$filenames,$preProcess,$packageInfos,$source='defineErrorStructure',$target='errorStructure',$messages=''){
			if(empty($messages))
				$messages = $this->_messages;
			
			$this->parsedFiles = $parsedFiles;
			
			foreach($parsedFiles as $parsedFileIndex=>$parsedFile){
				$newparsedFile = array();
				foreach($parsedFile as $kk=>$block){
					if(!isset($block['local'][$target]) || empty($block['local'][$target]))
						continue;
					foreach($block['local'][$target] as $k=>$definition){
						$name = $definition['name'];
						$version = isset($block['local']['version'])&&!empty($block['local']['version'])?$block['local']['version']:'0.0.0';
						
						if((!isset($preProcess[$source]) || empty($preProcess[$source])) || (!isset($preProcess[$source][$name]) || empty($preProcess[$source][$name]))){
							//引用组名称不存在 并没有被@apiDefine定义
							throw new Exception('Referenced groupname does not exist / it is not defined with @apiDefine.file:'.$filenames[$parsedFileIndex].'block:'.$block['index'].'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example'].'groupname:'.$name);
						}
						
						$matchedData = array();
						
						if(isset($preProcess[$source][$name][$version])&&!empty($preProcess[$source][$name][$version])){
							$matchedData = $preProcess[$source][$name][$version];
						}else{
							//匹配最高的版本
							$foundIndex = -1;
							$lastVersion = '0.0.0';
							
							$versionKeys = array_keys($preProcess[$source][$name]);
							
							foreach($versionKeys as $versionIndex=>$currentVersion){
								if($this->gte($version,$currentVersion) && $this->gte($currentVersion,$lastVersion)){
									$lastVersion = $currentVersion;
									$foundIndex = $versionIndex;
								}
							}
							
							//版本错误
							if($foundIndex === -1)
								throw new Exception('Referenced definition has no matching or a higher version. Check version number in referenced define block.file:'.$filenames[$parsedFileIndex].'block:'.$block['index'].'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example'].'groupname:'.$name);
							
							$versionName = $versionKeys[$foundIndex];
							$matchedData = $preProcess[$source][$name][$versionName];
						}
						// remove target, not needed anymore
						// TODO: create a cleanup filter
						unset($block['local'][$target]);//default : $block.local.errorStructure
						//尽可能的合并
						
						$block['local'] = $this->_recursiveMerge($block['local'],$matchedData);
					}
					$this->parsedFiles[$parsedFileIndex][$kk] = $block;
				}
				if(!empty($newparsedFile)){
					$newparsedFiles[$parsedFileIndex] = $newparsedFile;
				}
			}
			
		}
		
		//递归合并
		private function _recursiveMerge($a,$b){
			foreach($b as $k=>$v){
				if(is_array($v)){
					if(!isset($a[$k])){
						$a[$k] = $v;
					}else{
						if($this->is_assoc($v))
							$a[$k] = $this->_recursiveMerge($a[$k],$v);
						else
							$a[$k] = array_merge($a[$k],$v);
					}
				}
			}
			
			return $a;
		}
		
		private function is_assoc($array) {  
			return (bool)count(array_filter(array_keys($array), 'is_string'));  
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