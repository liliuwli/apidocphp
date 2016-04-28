<?php
	namespace apidoc\Parser;
	use apidoc\Parser\apiParam;
	
	class apiheader extends apiParam{
		protected $patten = '/^\s*(?:\(\s*(.+?)\s*\)\s*)?\s*(?:\{\s*([a-zA-Z0-9\(\)#:\.\/\\\\[\]_-]+)\s*(?:\{\s*(.+?)\s*\}\s*)?\s*(?:=\s*(.+?)(?=\s*\}\s*))?\s*\}\s*)?(\[?\s*([a-zA-Z0-9\:\.\/\\_-]+(?:\[[a-zA-Z0-9\.\/\\_-]*\])?)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(.*?)(?:\s*\]|$)))?\s*\]?\s*)(.*)?$|@/';
		protected $method = 'push';
		protected $markdownFields = array('description');
		
		public function parse($content){
			preg_match($this->patten,$content,$this->res);
			$allowedValues = $this->res[4];
			if(!empty($allowedValues)){
				if($allowedValues[0]==='\''){
					$RegExp = "/\'[^\']+\'/";
					preg_match_all($RegExp,$allowedValues,$res);
				}elseif($allowedValues[0]==='\''){
					$RegExp = '/\"[^\"]+\"/';
					preg_match_all($RegExp,$allowedValues,$res);
				}else{
					$RegExp = '/[^,\s]+/';
					preg_match_all($RegExp,$allowedValues,$res);
				}
				$allowedValues = $res[0];
			}
			$this->group = !empty($this->res[1])?$this->res[1]:'Parameter';
			$res = array(
				'group'	=>	$this->group,
				'type'	=>	$this->res[2],
				'size'	=>	$this->res[3],
				'allowedValues'	=>	$allowedValues,
				'optional'	=>	isset($this->res[5])&&$this->res[5][0]==='['?true:false,
				'field'		=>	$this->res[6],
				'defaultValue'	=>	empty($this->res[7])?empty($this->res[8])?$this->res[9]:$this->res[8]:$this->res[7],
				'description'	=>	$this->res[10]
			);
			return $res;
		}
		
		public function getGroup(){
			return $this->group;
		}
		
		public function getPath(){
			return 'local.header.fields.'.$this->getGroup();
		}
	}