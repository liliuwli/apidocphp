<?php
	class Filter{
		private $app;						//appobj
		private $filters;					//class variables
		
		function __construct($obj){
			$apiPath = dirname(__FILE__).DIRECTORY_SEPARATOR;
			$this->app = $obj;
			$workerSpace = '\apidoc\Filter\\';
			foreach($this->app->filters as $k=>$v){
				require $apiPath.$v;
				$this->app->logger->verbose('load filter:'.basename($v));
				$classname = $workerSpace.$k;
				$this->filters[$k] = new $classname();
			}
		}
		
		public function process($parsedFiles,$parsedFilenames){
			foreach($this->filters as $name=>$filter){
				if(method_exists($filter,'postFilter')){
					$this->app->logger->verbose('filter postFilter:'.$name);
					$filter->postFilter($parsedFiles,$parsedFilenames);
					$parsedFiles = $filter->parsedFiles;
				}
			}
			
			$blocks = array();
			
			foreach($parsedFiles as $parsedFile){
				foreach($parsedFile as $block){
					if(count($block['global']) === 0 && count($block['local']) >0){
						$blocks[] = $block['local'];
					}
				}
			}
			
			return $blocks;
		}
	}