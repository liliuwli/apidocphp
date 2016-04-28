<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apipermission extends ParserDriver{
		protected $path = 'local.permission';
		protected $method = 'push';
		protected $preventGlobal = true;
		
		public function parse($content){
			$name = trim($content);
			if (empty($name))
				return null;
			return array(
				'name'=>$name
			);
		}
	}