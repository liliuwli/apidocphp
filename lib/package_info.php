<?php
/*
	获取外部或者默认配置文件
*/
class PackageInfo{
	private $app;
	
	private function _getHeaderFooter($json){
		$result = array();
		foreach(array('header', 'footer') as $v){
			if((isset($json[$v])&&$json[$v]) && (isset($json[$v]['filename'])&&$json[$v]['filename'])){
				$dir = $this->_resolveSrcPath();
				$filename = $dir.$json[$v]['filename'];
				
				if(!file_exists($filename))
					$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'../example/'.$json[$v]['filename'];
				
				try{
					$this->app->log->debug('read header file: '.$filename);
					$content = file_get_contents($filename);
					
					$result[$v] = array(
						'title'=>$json[$v]['title'],
						'content'=>isset($this->app->markdownParser)&&$this->app->markdownParser?$this->app->markdownParser->render($content):$content,
					);
				}catch(Exception $e){
					echo 'message:'.$e->getMessage();
				}
			}
		}
		return $result;
	}
	
	public function setapp($app){
		$this->app = $app;
	}
	
	public function get(){
		$result = array();
		
		$packageJson = $this->_readPackageData('package.json');
		
		if(isset($packageJson['apidoc'])&&$packageJson['apidoc'])
			$result = $packageJson['apidoc'];
		
		$result['name'] = isset($packageJson['name'])&&$packageJson['name']?$packageJson['name']:'';
		$result['version'] = isset($packageJson['version'])&&$packageJson['version']?$packageJson['version']:'0.0.0';
		$result['description'] = isset($packageJson['description'])&&$packageJson['description']?$packageJson['description']:'';
		
		$apidocJson = $this->_readPackageData('apidoc.json');
		
		foreach($apidocJson as $k=>$v){
			$result[$k] = $v;
		}
		
		if(isset($this->app->options->packageInfo)){
			foreach($this->app->options->packageInfo as $k=>$v){
				$result[$k] = $v;
			}
		}
		
		$end = $this->_getHeaderFooter($result);
		
		foreach($end as $k=>$v){
			$result[$k] = $v;
		}
		
		if(count($apidocJson)===0 && !$packageJson['apidoc']){
			$this->app->log->warn('Please create an apidoc.json configuration file.');
		}
		return $result;
	}
	
	//读取当前cmd目录json数据 不存在则是apidoc目录
	private function _readPackageData($filename){
		$dir = $this->_resolveSrcPath();
		
		$jsonFilename = $dir.$filename;
		
		if(!file_exists($jsonFilename)){
			$jsonFilename = $this->app->options['config'].$filename;
		}
		
		if(!file_exists($jsonFilename)){
			$this->app->log->warn('Please create an apidoc.json configuration file.');
		}else{
			try{
				$result = json_decode(file_get_contents($jsonFilename),true);
				$this->app->log->debug('read:'.$filename);
			}catch(Exception $e){
				echo 'message:'.$e->getMessage();
			}
		}
		
		return $result;
	}
	
	//获取当前cmd目录
	private function _resolveSrcPath(){
		$dir = './';
		
		if(is_array($this->app->options['src']) && count($this->app->options['src'])===1){
			$dir = $this->app->options['src'][0].DIRECTORY_SEPARATOR;
		}
		
		return $dir;
	}
}

return new PackageInfo();