<?php
	class FindFiles{
		private $path = '';
		private $options = array();
		private $excludeFilters = array();
		private $includeFilters;
		
		public function __construct($folderOptions){
			$this->options = $folderOptions;
		}
		
		public function search(){
			//过滤规则处理	包含的过滤
			if(is_string($this->includeFilters)){
				$this->includeFilters = array($this->includeFilters);
			}
			
			//不包含的过滤  优先级更高
			if(is_string($this->excludeFilters)){
				$this->excludeFilters = array($this->excludeFilters);
			}
			
			$files = $this->recfiles();
			$files = array_filter($files,array($this,'includefilter'));
			$files = array_filter($files,array($this,'excludefilters'));
			return $files;
		}
		
		public function setPath($path){
			$this->path = $path;
		}
		
		public function setExcludeFilters($excludeFilters){
			$this->excludeFilters = $excludeFilters;
		}
		
		public function setIncludeFilters($includeFilters){
			$this->includeFilters = $includeFilters;
		}
		
		private function includefilter(&$filename){
			//判断是不是windows
			if(PATH_SEPARATOR==';')
				$filename = preg_replace('/\//',DIRECTORY_SEPARATOR,$filename);
			
			foreach($this->includeFilters as $v){
				preg_match($v,$filename,$res);
				if(empty($res)){
					return false;
				}
				return true;
			}
		}
		
		private function excludeFilters($filename){
			foreach($this->excludeFilters as $v){
				preg_match($v,$filename,$res);
				if(empty($res)){
					return true;
				}
				return false;
			}
			return true;
		}
		
		//递归获取文件 返回一维数组
		private function recfiles($dir=''){
			if(empty($dir)){
				$dir = $this->path;
			}
			$files =array();
			if(is_dir($dir) && file_exists($dir)){
				$fsdir = opendir($dir);
				while(($file=readdir($fsdir)) !== false){
					if($file == '..' || $file == '.'){
						continue;
					}
					
					if(is_dir($sondir = $dir.DIRECTORY_SEPARATOR.$file) && $file != basename($this->options['dest'])){
						$files = array_merge($files,$this->recfiles($sondir));
					}else{
						$files[] = $sondir;
					}
				}
				closedir($fsdir);
			}
			return $files;
		}
	}