<?php
	interface logger{
		function debug($content);
		function verbose($content);
		function info($content);
		function warn($content);
		function error($content);
	}
	
	class applog implements logger{
		public $info = array();
		public $debug = array();
		
		public function debug($content){
			$this->debug[] = $content;
		}
		
		public function verbose($content){
			$this->info[] = $content;
		}
		
		function info($content){
			exit('info:'.$content);
		}
		
		function warn($content){
			exit('warn :'.$content);
		}
		
		function error($content){
			
		}
	}
	
	class apidoc{
		public $PackageInfo;
		private $apidocPath;
		private $default = array(
			'dest'    => './doc/',
			'template'=> './template/',
			'debug'   => false,
			'silent'   => false,
			'verbose'   => false,
			'simulate'   => false,
			'parse'   => false,
			'colorize'=> true,
			'markdown'=> true,
			'config'  => './',
			'lineEnding'=>PHP_EOL,
			'src'		=>array(0=>''),
		);
		public $options;
		private $apidoc;
		public $log;
		
		function __construct(){
			$this->apidocPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'../';
			$this->PackageInfo = require dirname(__FILE__).DIRECTORY_SEPARATOR.'package_info.php';
			$this->apidoc = require $this->apidocPath.'core/index.php';
			//配置默认值
			$this->default['template']=$this->apidocPath.$this->default['template'];
			$this->default['config']=$this->apidocPath.$this->default['config'];
			$this->default['src'][0]=$this->apidocPath.$this->default['src'][0];
		}
		
		function createDoc($options=array()){
			foreach($this->default as $k=>$v){
				$this->options[$k] = isset($options[$k])?$options[$k]:$v;
			}
			$this->options['dest'] = $options['src'][0].DIRECTORY_SEPARATOR.$this->default['dest'];
			//模板渲染插件（待增）功能:  输入特定符号+文字  生成html标签   用途  处理header、footer
			
			//项目配置
			$json = json_decode(file_get_contents($this->apidocPath.'package.json'),true);
			
			//核心设置
			$this->apidoc->setGeneratorInfos(array(
				'name'=>$json['name'],
				'time'=>date('Y-m-d h:i:s'),
				'url'=>$json['homepage'],				//node版本 apidocjs.com
				'version'=>$json['version'],
			));
			
			//设置日志类
			$applog = new applog();
			$this->apidoc->setLogger($applog);
			$this->log = $applog;
			
			//处理配置文件
			$this->PackageInfo->setapp($this);
			$this->apidoc->setPackageInfos($this->PackageInfo->get());
			
			$api = $this->apidoc->parse($this->options);
			
			if($api === true){
				$this->log->info('nothing to do');
			}
			
			if($api === false){
				return false;
			}
			
			if($this->options['parse'] !== true)
				$this->createOutputFiles($api);
			
			$this->log->info('Done');
			return $api;
		}
		
		//最后一步 创建doc文件夹
		private function createOutputFiles($api){
			if($this->options['simulate'])
				$this->log->warn('!!! Simulation !!! No file or dir will be copied or created.');
			
			$this->log->verbose('create dir: '.$this->options['dest']);
			if(!file_exists($this->options['dest']))
				mkdir($this->options['dest']);
			
			//递归复制
			$this->log->verbose('copy template ' . $this->options['template'] . ' to: ' . $this->options['dest']);
			$this->recurse_copy($this->options['template'],$this->options['dest']);
			
			//数据生成
			$this->log->verbose('write json file: ' . $this->options['dest'] . 'api_data.json' . $this->options['template'].'api_data.json');
			file_put_contents($this->options['dest'].'api_data.json',utf8_encode($api['data']));
			$this->log->verbose('write js file: ' . $this->options['dest'] . 'api_data.js' . $this->options['template'].'api_data.js');
			file_put_contents($this->options['dest'].'api_data.js','define({ "api": '.utf8_encode($api['data']).' });');
			$this->log->verbose('write json file: ' . $this->options['dest'] . 'api_project.json' . $this->options['template'].'api_project.json');
			file_put_contents($this->options['dest'].'api_project.json',utf8_encode($api['project']));
			$this->log->verbose('write js file: ' . $this->options['dest'] . 'api_project.js' . $this->options['template'].'api_project.js');
			file_put_contents($this->options['dest'].'api_project.js','define('.utf8_encode($api['project']).');');
			
		}
		
		//递归复制
		private function recurse_copy($src,$dst){
			$dir = opendir($src);
			@mkdir($dst);
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($src . '/' . $file) ) {
						$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
					}
					else {
						copy($src . '/' . $file,$dst . '/' . $file);
					}
				}
			}
			closedir($dir);
		}
	}
	
	//获取cmd地址，直接调用，直接屏蔽的了
	if(!isset($argv)){
		header("Content-type: text/html; charset=utf-8");
		exit('请使用命令行调用');
	}
	
	if(isset($argv[1]) && $argv[1]){
		$dir = $argv[1];
	}else{
		$dir = getcwd();
	}
	
	$options = array(
		'src'=>array(
			0=>$dir
		)
	);
	
	$apidoc = new apidoc();
	$res = $apidoc->createDoc($options);