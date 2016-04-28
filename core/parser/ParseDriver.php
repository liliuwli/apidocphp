<?php
	namespace apidoc\Parser;
	use apidoc\Parser\interfaceDriver;
	class ParserDriver implements interfaceDriver{
		Protected $path;			//local or global
		Protected $method;			//insert or push
		Protected $patten;			//RegExp
		Protected $res;				//after RegExp 
		public function __construct(){
			
		}
		
		public function parse($content){
			
		}
		
		public function getPath(){
			return $this->path;
		}
		
		public function getMethod(){
			return $this->method;
		}
	}