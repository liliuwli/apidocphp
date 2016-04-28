<?php
	namespace apidoc\Worker;
	class apisuccesstitle{
		//错误提示信息
		protected $_messages = array(
			'common' => array(
				'element'	=>'apiSuccess',
				'usage'		=>'@apiSuccess (group) varname',
				'example'	=>'@apiDefine MyValidSuccessGroup Some title or 200 OK\n@apiSuccess (MyValidSuccessGroup) username',
			)
		);
		
		public $parsedFiles = array();
		
		//预处理		返回全局定义的内容
		public function preProcess($parsedFiles,$filenames,$packageInfos,$target='defineSuccessTitle'){
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
		
		//正式处理		定义的内容赋予继承者
		public function postProcess($parsedFiles,$filenames,$preProcess,$packageInfos,$source='defineSuccessTitle',$target='success',$messages=''){
			if(empty($messages))
				$messages = $this->_messages;
			
			$this->parsedFiles = $parsedFiles;
			
			foreach($parsedFiles as $parsedFileIndex=>$parsedFile){
				
				foreach($parsedFile as $k=>$block){
					if(!isset($block['local'][$target]) || empty($block['local'][$target]))
						continue ;
					
					$newFields =array();
					$fields = $block['local'][$target]['fields'];
					foreach($fields as $fieldGroup=>$params){
						foreach($params as $definition){
							$name = $definition['group'];
							$version = isset($block['local']['version'])&&!empty($block['local']['version'])?$block['local']['version']:'0.0.0';
							$matchedData = array();
							
							if((!isset($preProcess[$source]) || empty($preProcess[$source])) || (!isset($preProcess[$source][$name]) || empty($preProcess[$source][$name]))){
								$matchedData['title'] = $name;
								$matchedData['name'] = $name;
							}else{
								if(isset($preProcess[$source][$name][$version])&&!empty($preProcess[$source][$name][$version])){
									$matchedData = $preProcess[$source][$name][$version];
								}else{
									$foundIndex = -1;
									$lastVersion = '0.0.0';
									$versionKeys = array_keys($preProcess[$source][$name]);
									foreach($versionKeys as $versionIndex=>$currentVersion){
										if($this->gte($version,$currentVersion) && $this->gte($currentVersion,$lastVersion)){
											$lastVersion = $currentVersion;
											$foundIndex = $versionIndex;
										}
									}
									if($foundIndex === -1)
										throw new Exception('Referenced definition has no matching or a higher version. Check version number in referenced define block.file:'.$filenames[$parsedFileIndex].'block:'.$block['index'].'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example'].'Groupname:'.$name.'version:'.$version.'Defined versions'.$versionKeys);
								}
								$versionName = $versionKeys[$foundIndex];
								$matchedData = $preProcess[$source][$name][$versionName];
							}
							
							if (!isset($newFields[$matchedData['title']]) || empty($newFields[$matchedData['title']]))
								$newFields[$matchedData['title']] = [];

							$newFields[$matchedData['title']][] = $definition;
						}
					}
					$block['local'][$target]['fields'] = $newFields;
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