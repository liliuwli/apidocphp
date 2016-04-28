<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apiName extends ParserDriver{
		protected $patten = '/(\s+)/';
		protected $path = 'local';
		protected $method = 'insert';
		public function __construct(){
			
		}
		
		public function parse($content){
			$res = preg_replace($this->patten,'_',$content);
			return array(
				'name'=>$res
			);
		}
	}