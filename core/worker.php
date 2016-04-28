<?php
	class Worker{
		private $app;						//appobj
		private $workers;					//class variables
		public $parsedFiles = array();				
		function __construct($obj){
			$apiPath = dirname(__FILE__).DIRECTORY_SEPARATOR;
			$this->app = $obj;
			$workerSpace = '\apidoc\Worker\\';
			foreach($this->app->workers as $k=>$v){
				require $apiPath.$v;
				$this->app->logger->verbose('load worker:'.basename($v));
				$classname = $workerSpace.$k;
				$this->workers[$k] = new $classname();
			}
		}
		
		public function process($parsedFiles,$parsedFilenames,$packageInfos){
			foreach($parsedFiles as $fileIndex=>&$parsedFile){
				foreach($parsedFile as &$block){
					if(count($block['global'])===0 && count($block['local'])>0){
						if(!isset($block['local']['type']) && empty($block['local']['type']))
							$block['local']['type'] = '';
						if(!isset($block['local']['url']) && empty($block['local']['url']))
							$block['local']['url'] = '';
						if(!isset($block['local']['version']) && empty($block['local']['version']))
							$block['local']['version'] = '0.0.0';
						if(!isset($block['local']['filename']) && empty($block['local']['filename']))
							$block['local']['filename'] = $parsedFilenames[$fileIndex];
						$block['local']['filename'] = str_replace('\\','/',$block['local']['filename']);
					}
				}
			}
			
			$preProcessResults = array();
			
			foreach($this->workers as $name=>$worker){
				if(method_exists($worker,'preProcess')){
					$this->app->logger->verbose('worker preProcess: '.$name);
					$result = $worker->preProcess($parsedFiles,$parsedFilenames,$packageInfos);
					
					$preProcessResults = array_merge($preProcessResults,$result);
					
				}
			}
			
			
			$this->parsedFiles = $parsedFiles;
			foreach($this->workers as $name=>$worker){
				if(method_exists($worker,'postProcess')){
					$this->app->logger->verbose('worker postProcess: '.$name);
					$worker->postProcess($this->parsedFiles,$parsedFilenames,$preProcessResults,$packageInfos);
					$this->parsedFiles = $worker->parsedFiles;
				}
			}
		}
	}