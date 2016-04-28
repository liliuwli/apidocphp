<?php
	require $apiPath.'utils/find_files.php';
	class Parser{
		private $app;						//appobj
		private $parsers 		= array();	//解析类集合
		private $languages 		= array();	//多语言包集合
		public $parsedFiles 	= array();	//装载解析内容
		public $parsedFilenames= array();	//装载文件名
		private $linebreak =  'hhhhh';		//换行符
		private $countDeprecated = array();//废弃标签容器
 		
		function __construct($obj){
			$apiPath = dirname(__FILE__).DIRECTORY_SEPARATOR;
			$this->app = $obj;
			// load languages
			foreach($this->app->languages as $k=>$v){
				$this->languages[$k] = require($apiPath.$v);
				$this->app->logger->verbose('parse languages file: '.basename($v));
			}
			
			// load parser
			require $apiPath.'parser/Driver.php';
			require $apiPath.'parser/ParseDriver.php';
			$parseSpace = '\apidoc\Parser\\';
			foreach($this->app->parsers as $k=>$v){
				require $apiPath.$v;
				$this->app->logger->verbose('parse file: '.basename($v));
				$classname = $parseSpace.$k;
				$this->parsers[$k] = new $classname();
			}
		}
		
		public function parseFiles($folderOptions){
			//获取文件集合
			$findFiles = new FindFiles($folderOptions);
			$findFiles->setPath($folderOptions['src']);
			$findFiles->setExcludeFilters($folderOptions['excludeFilters']);
			$findFiles->setIncludeFilters($folderOptions['includeFilters']);
			$files = $findFiles->search();
			
			//Parser
			foreach($files as $filedir){
				$filename = basename($filedir);
				$parsedFile = $this->parseFile($filedir);
				
				if(!empty($parsedFile)){
					$this->app->logger->verbose('parse file: '.$filename);
					$this->parsedFiles[] = $parsedFile;
					$this->parsedFilenames[] = $filename;
				}
			}
		}
		
		private function parseFile($filedir){
			$filename = basename($filedir);
			$this->app->logger->debug('inspect file: '.$filename);
			
			//取得文件信息
			$extension = pathinfo($filedir, PATHINFO_EXTENSION);
			$content = file_get_contents($filedir);
			$this->app->logger->debug('inspect file: '.filesize($filedir));
			
			//统一换行
			$content = preg_replace('/\r\n/',"\n",$content);
			
			$block = array();									//块注释容器
			$indexApiBlocks = array();							//可用块索引集合
			
			$blocks = $this->_findBlocks($content,$extension);	//捕获块注释
			
			if(empty($blocks))
				return ;
			
			$this->app->logger->debug('count blocks: '.count($blocks));
			
			$elements = array();
			
			foreach($blocks as $k=>$block){
				 $element = $this->_findElements($block);
				 $elements[] = $element;
				 $this->app->logger->debug('count elements in block '.$k.':'.count($element));
			}
			
			if(empty($elements))
				return ;
			
			$indexApiBlocks = $this->_findBlockWithApiGetIndex($elements);
			
			if(empty($indexApiBlocks))
				return ;
			
			
			return $this->_parseBlockElements($indexApiBlocks,$elements,$filename);
		}
		
		private function _parseBlockElements($indexApiBlocks,$detectedElements,$filename){
			$parsedBlocks = array();
			foreach($indexApiBlocks as $blockIndex){
				$elements = $detectedElements[$blockIndex];
				
				//后者收集继承的元素
				$blockData = array(
					'local'=>array(),
					'global'=>array()
				);
				$countAllowedMultiple = 0;
				
				
				foreach($elements as $k=>$element){
					if(!isset($this->parsers[$element['name']]) || empty($this->parsers[$element['name']])){
						$this->app->logger->warn('parser plugin \''.$element['name'].'\' not found in block: '.$blockIndex);
					}
					$elementParser = $this->parsers[$element['name']];
					
					$this->app->logger->debug('found @'.$element['sourceName'].' in block: '.$blockIndex);
					
					//当标签废弃时
					if(isset($elementParser->deprecated) && !empty($elementParser->deprecated)){
						$this->countDeprecated[$element['name']] = empty($this->countDeprecated[$element['name']])? 1 :$this->countDeprecated[$element['name']]++;
						$message = '@'.$element['name'].' is deprecated';
						//是否有新取代的标签
						if(isset($elementParser->alternative) && !empty($elementParser->alternative)){
							$message = '@'.$element['name'].' is deprecated, please use '.$elementParser->alternative;
						}
						
						//错误信息仅提示一次
						if($this->countDeprecated[$element['name']] === 1)
							$this->app->logger->warn($message);
						else
							$this->app->logger->verbose($message);
						
						$this->app->logger->verbose('in file: '.$filename.', block: '.$blockIndex);
					}
					
					try{
						// parse element and retrieve values     
						$values = $elementParser->parse($element['content'],$element['source']);
						
						// check if it is allowed to add to global namespace
						$preventGlobal = isset($elementParser->preventGlobal) && $elementParser->preventGlobal===true;
						
						// allow multiple inserts into pathTo
						$allowMultiple = isset($elementParser->allowMultiple) && $elementParser->allowMultiple===true;
						
						// path to an array, where the values should be attached
						$pathTo = $elementParser->getPath();
						
						
						if(empty($pathTo))
							throw new Exception('pathTo is not defined in the parser file:'.$element['name']);
						
						// method how the values should be attached (insert or push)
						$attachMethod = $elementParser->getMethod();
						
						if ($attachMethod !== 'insert' && $attachMethod !== 'push')
							throw new Exception('Only push or insert are allowed parser method values:'.$element['name']);
						
						// TODO: put this into "converters"
						if(!empty($values)){
							if($this->app->markdownParser&&isset($elementParser['markdownFields'])&&!empty($elementParser['markdownFields'])){
								foreach($elementParser['markdownFields'] as $field){
									//e.g. 'description','type'
									if($values[$field]){
										$values[$field] = $this->app->render($values[$field]);
										
										// remove line breaks
										$values[$field] = preg_replace('/(\r\n|\n|\r)/','',$values[$field]);
										$values[$field] = trim($values[$field]);
										
										//解决指定字段的p标签
										if(isset($elementParser['markdownRemovePTags']) && !empty($elementParser['markdownRemovePTags']) && in_array($field,$elementParser['markdownRemovePTags'])){
											$values[$field] = preg_replace('/(<p>|<\/p>)/','',$values[$field]);
										}
									}
								}
							}
						}
					}catch(Exception $e){
						echo 'message:'.$e->getMessage();
					}
					
					if(empty($values))
						throw new Exception('Empty parser result.filename:'.$filename.'block:'.$k.'name:'.$element['name']);
					
					//是否继承机制
					if($preventGlobal){
						//如果全局元素是否超过上限
						if(count($blockData['global'])>$countAllowedMultiple)
							throw new Exception('Only one definition or usage is allowed in the same block.filename:'.$filename.'block:'.$k.'name:'.$element['name']);
					}
					
					// only one global allowed per block
					if($pathTo === 'global' || substr($pathTo,0,7) === 'global.'){
						if ($allowMultiple) {
							$countAllowedMultiple++;
						}else{
							if(count($blockData['global'])>0){
								throw new Exception('Only one definition or usage is allowed in the same block.filename:'.$filename.'block:'.$k.'name:'.$element['name']);
							}
							
							if($preventGlobal === true){
								throw new Exception('Only one definition or usage is allowed in the same block.filename:'.$filename.'block:'.$k.'name:'.$element['name']);
							}
						}
					}
					
					//如果非单重路径 e.g. local.new  创建对应的变量
					if(!isset($blockData[$pathTo]))
						$blockData = $this->_createObjectPath($blockData,$pathTo);
					
					//返回指令
					$com = $this->_pathToObject($pathTo);
					eval('$blockDataPath = '.$com);
					
					if($attachMethod ==='push')
						$blockDataPath[] = $values;
					else
						$blockDataPath = array_merge($blockDataPath,$values);
					
					// insert Fieldvalues in Mainpath
					if(isset($elementParser->extendRoot)&&$elementParser->extendRoot === true)
						$blockData = array_merge($blockData,$values);
					
					
				}
				$blockIndex++;
				$blockData['index'] = $blockIndex;
				
				if(isset($blockData['index']) && $blockData['index'] > 0)
					$parsedBlocks[]= $blockData;
			}
			
			return $parsedBlocks;
		}
		
		private function _pathToObject($pathTo){
			$com = '&$blockData';
			if(empty($pathTo))
				return $com;
			$pathParts = explode('.',$pathTo);
			
			foreach($pathParts as $v){
				$com .= '[\''.$v.'\']';
			}
			$com .=';';
			
			return $com;
		}
		
		private function _createObjectPath($blockData,$pathTo){
			if(empty($pathTo))
				return $blockData;
			$pathParts = explode('.',$pathTo);
			//中间量
			$current = $blockData;
			
			$index = '$blockData';
			foreach($pathParts as $k=>$part){
				$index .= '[\''.$part.'\']';
				if(isset($current[$part])){
					//已有的层级 直接进入
					$current = $current[$part];
				}else{
					//创建多层级结构
					eval($index.'=array();');
					$current = array();
				}
			}
			return $blockData;
		}
		
		//preg_replace 替换\r\n 为\n 
		private function _findBlocks($content,$ext){
			// Replace Linebreak with custom hhhhh
			$content = preg_replace('/\n/',$this->linebreak,$content);
			
			$regex = isset($this->languages[$ext]) && !empty($this->languages[$ext])?$this->languages[$ext]:$this->languages['default'];
			
			preg_match_all($regex['docBlocksRegExp'],$content,$matches,PREG_SET_ORDER);
			
			if(empty($matches)){
				return ;
			}
			
			$blocks = array();
			foreach($matches as $v){
				$block = isset($v[2])&&!empty($v[2])?$v[2]:$v[1];
				//取回换行  多行排除干扰 *
				$block = preg_replace('/'.$this->linebreak.'/',"\n",$block);
				//清除开头的* 和顶头的空格
				$block = preg_replace($regex['inlineRegExp'],'',$block);
				$blocks[] = $block;
			}
			return $blocks;
		}
		
		private function _findElements($block){
			//每个块的元素集合
			$block = preg_replace('/\n/',$this->linebreak,$block);
			$RegExp = '/(@(\w*)\s?(.+?)(?='.$this->linebreak.'[\s\*]*@|$))/m';
			preg_match_all($RegExp,$block,$matches,PREG_SET_ORDER);
			$elements = array();
			foreach($matches as $matche){
				$elements[] = array(
					'source'	=>str_replace($this->linebreak,"\n",$matche[1]),					//原内容
					'name'		=>strtolower($matche[2]),		//小写标签名称	api
					'sourceName'=>$matche[2],					//用户书写标签名称
					'content'	=>str_replace($this->linebreak,"\n",$matche[3])					//标签内容
				);
			}
			
			return $elements;
		}
		
		
		private function _findBlockWithApiGetIndex($elements){
			$foundIndexes = array();
			
			foreach($elements as $k=>$v){
				$found = false;
				foreach($v as $element){
					if(substr($element['name'],0,9) === 'apiignore'){
						$this->app->logger->debug('apiIgnore found in block:'.$k);
						$found = false;
						break;
					}
					
					if(substr($element['name'],0,3) === 'api'){
						$found = true;
					}
				}
				if ($found){
					$foundIndexes[]=$k;
					$this->app->logger->debug('api found in block: '.$k);
				}
			}
			
			return $foundIndexes;
		}
		
	}