<?php
	function recfiles($dir=''){
		if(empty($dir)){
			$dir = dirname(__FILE__).DIRECTORY_SEPARATOR;
		}
		$files =array();
		if(is_dir($dir) && file_exists($dir)){
			$fsdir = opendir($dir);
			while(($file=readdir($fsdir)) !== false){
				if($file == '..' || $file == '.' || $file=='template'|| $file=='example'){
					continue;
				}
				if(is_dir($sondir = $dir.DIRECTORY_SEPARATOR.$file)){
					$files = array_merge($files,recfiles($sondir));
				}else{
					$files[] = $sondir;
				}
			}
			closedir($fsdir);
		}
		return $files;
	}
	
	$files = recfiles();
	
	foreach($files as $v){
		$content = file_get_contents($v);
		$res = preg_match('/groupDescription/',$content,$match);
		if($res){
			echo $v.'<br>';
		}
	}