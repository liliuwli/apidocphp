<?php
	$apiPath = dirname(__FILE__).DIRECTORY_SEPARATOR;
	
	//引入文件
	require($apiPath.'parse.php');
	require($apiPath.'filter.php');
	require($apiPath.'worker.php');
	//版本
	define("SPECIFICATION_VERSION",'0.2.0');
	
	//e.g. class app.field
	$defaults=array(
		'excludeFilters'=>array(),
		'includeFilters'=>array(
			'/.*\.(clj|coffee|cs|dart|erl|exs?|go|groovy|java|js|litcoffee|php?|py|rb|scala|ts|pm)$/',
		),
		'src'=>array(
			'../example',
		),
		'filters'=>array(
			
		),
		'languages'=>array(
		
		),
		'parsers'=>array(
			
		),
		'workers'=>array(
			
		),
		'lineEnding'=>PHP_EOL
	);
	
	
	
	//apidoc-conf
	$defaultPackageInfos = array(
		'description'=> '',
		'name'       => '',
		'sampleUrl'  => 'www.test.com',
		'version'    => '0.0.0'
	);
	
	
	
	class app{
		private $packageInfos;
		public $markdownParser = false;
		public $logger;				//日志工具类
		private $generator;
		private $Parser;
		public $languages = array(
			'.clj'                   => './languages/clj.php',
			'.coffee'                => './languages/coffee.php',
			'.erl'                   => './languages/erl.php',
			'.ex'                    => './languages/ex.php',
			'.exs'                   => './languages/ex.php',
			'.litcoffee'             => './languages/coffee.php',
			'.pm'                    => './languages/pm.php',
			'.py'                    => './languages/py.php',
			'.rb'                    => './languages/rb.php',
			'default'                => './languages/default.php'
		);
		
		public $parsers = array(
			'api'				=>'./parser/api.php',
			'apigroup'			=>'./parser/api_group.php',
			'apiexample'		=>'./parser/api_example.php',
			'apiname'			=>'./parser/api_name.php',
			'apiparam'			=>'./parser/api_param.php',
			'apiheader'			=>'./parser/api_header.php',
			'apiheaderexample'	=>'./parser/api_header_example.php',
			'apisuccessexample'	=>'./parser/api_success_example.php',
			'apisuccess'		=>'./parser/api_success.php',
			'apidefine'			=>'./parser/api_define.php',
			'apiversion'		=>'./parser/api_version.php',
			'apierror'			=>'./parser/api_error.php',
			'apierrorexample'	=>'./parser/api_error_example.php',
			'apipermission'     =>'./parser/api_permission.php',
			'apiuse'     		=>'./parser/api_use.php',
			'apidescription'    =>'./parser/api_description.php',
		);
		
		public $workers = array(
			'apierrorstructure'        => './worker/api_error_structure.php',
			'apierrortitle'            => './worker/api_error_title.php',
			'apigroup'                 => './worker/api_group.php',
			'apiheaderstructure'       => './worker/api_header_structure.php',
			'apiheadertitle'           => './worker/api_header_title.php',
			'apiname'                  => './worker/api_name.php',
			'apiparamtitle'            => './worker/api_param_title.php',
			'apipermission'            => './worker/api_permission.php',
			'apisamplerequest'         => './worker/api_sample_request.php',
			'apistructure'             => './worker/api_structure.php',
			'apisuccessstructure'      => './worker/api_success_structure.php',
			'apisuccesstitle'          => './worker/api_success_title.php',
			'apiuse'                   => './worker/api_use.php',
		);
		
		public $filters = array(
			'apiparam'                 => './filter/api_param.php',
			'apierror'                 => './filter/api_error.php',
			'apiheader'                => './filter/api_header.php',
			'apisuccess'               => './filter/api_success.php'
		);
		
		private $defaultGenerator;
		
		private $defaults;
		
		//default
		private $options;
		private $parsedFiles;
		private $parsedFilenames;
		
		public function __construct($option=array()){
			//处理文件的配置
			$this->defaults = $option;
			if(isset($option['filters']))
				$this->filters   = array_merge($option['filters'],$this->filters);
			if(isset($option['languages']))
				$this->languages = array_merge($option['languages'],$this->languages);
			if(isset($option['parsers']))
				$this->parsers   = array_merge($option['parsers'],$this->parsers);
			if(isset($option['workers']))
				$this->workers   = array_merge($option['workers'],$this->workers);
			
			$this->options = $option;
			
			if(isset($option['generator']))
				$this->generator   = array_merge($option['generator'],$this->generator);
			if(isset($option['packageInfos']))
				$this->packageInfos   = array_merge($option['packageInfos'],$this->packageInfos);
			
			$this->defaultGenerator = array(
				'name'   => 'apidoc',
				'time'   => date('Y-m-d h:i:s'),
				'url'    => 'http://apidocphp.com',
				'version'=> '0.0.0'
			);
		}
		
		public function parse($options=array()){
			//新的配置
			foreach($this->defaults as $k=>$v){
				if(!isset($options[$k])){
					$options[$k] = $v;
				}
			}
			
			$this->options = $options;
			
			try{
				//解析数组内的路劲   处理多个路劲
				$this->Parser = new Parser($this);
				//此处实例化  便于设置生效
				if(is_array($this->options['src'])){
					foreach($this->options['src'] as $v){
						$foldoptions = $this->options;
						$foldoptions['src'] = $v;
						$this->Parser->parseFiles($foldoptions);
					}
				}else{
					$foldoptions = $this->options;
					$foldoptions['src'] =  $foldoptions['src'];
					$this->Parser->parseFiles($foldoptions);
				}
				
				$parsedFiles = $this->Parser->parsedFiles;
				$parsedFilenames = $this->Parser->parsedFilenames;
				if(!empty($parsedFiles)){
					$this->logger->verbose('worker run');
					$this->Worker = new Worker($this);
					$this->Worker->process($parsedFiles,$parsedFilenames,$this->packageInfos);
					$parsedFiles = $this->Worker->parsedFiles;
					
					//filter
					$this->Filter = new Filter($this);
					$blocks = $this->Filter->process($parsedFiles,$parsedFilenames);
					
					// sort by group ASC, name ASC, version DESC
					$blocks = $this->tree($blocks);
					
					$this->packageInfos['apidoc'] = SPECIFICATION_VERSION;
					$this->packageInfos['generator'] = $this->generator;
					
					$apiData = json_encode($blocks);
					$apiData = preg_replace('/(\r\n|\n|\r)/',$this->options['lineEnding'],$apiData);
					
					
					
					$apiProject = json_encode($this->packageInfos);
					$apiProject = preg_replace('/(\r\n|\n|\r)/',$this->options['lineEnding'],$apiProject);
					
					return array(
						'data'=>$apiData,
						'project'=>$apiProject,
					);
				}
				return true;
			}catch(Exception $e){
				echo 'message:'.$e->getMessage();
				return false;
			}
		}
		
		/**
		 * Set package infos.
		 *
		 * @param {Object} [packageInfos]             Collected from apidoc.phpon / package.phpon.
		 * @param {String} [packageInfos.name]        Project name.
		 * @param {String} [packageInfos.version]     Version (semver) of the project, e.g. 1.0.27
		 * @param {String} [packageInfos.description] A short description.
		 * @param {String} [packageInfos.sampleUrl]   @see http=>//apidocphp.com/#param-api-sample-request
		 */
		public function setPackageInfos($packageInfos) {
			$this->packageInfos = $packageInfos;
		}
		
		public function setMarkdownParser($markdownParser) {
			$this->markdownParser = $markdownParser;
		}
		
		public function setLogger($logger) {
			//var_dump($logger);
			$this->logger = $logger;
		}
		
		public function setGeneratorInfos($generator = '') {
			$generator = $generator===''?$this->defaultGenerator:$generator;
			$this->generator = $generator;
		}
		
		private function tree($array){
			if(!is_array($array) || empty($array))
				return array();
			$len = count($array);
			if($len<= 1)
				return $array;
			$key[0] = $array[0];
			$left = array();
			$right = array();
			for($i = 1;$i<$len;$i++){
				if($this->recusort($array[$i],$key[0])){
					$right[] = $array[$i];
				}else{
					$left[] = $array[$i];
				}
			}
			$left = $this->tree($left);
			$right = $this->tree($right);
			return array_merge($left, $key, $right);
		}
		
		private function recusort($a,$b){
			$namea = $a['group'].$a['name'];
			$nameb = $b['group'].$b['name'];
			if($namea===$nameb){
				if($a['version'] === $b['version'])
					return false;
				return $this->gte($a['version'],$b['version'])?false:true;
			}
			return $namea>$nameb?true:false;
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
	
	function getSpecificationVersion() {
		return SPECIFICATION_VERSION;
	}
	
	$app = new app($defaults);
	$app->setPackageInfos($defaultPackageInfos);
	return $app;