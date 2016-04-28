<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apidescription extends ParserDriver{
		protected $path = 'local';
		protected $method = 'insert';
		protected $markdownFields = array('description');
		
		public function parse($content){
			$description = trim($content);
			if(empty($description))
				return null;
			return array(
				'description'=>ltrim($description)
			);
		}
	}