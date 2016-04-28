<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apiversion extends ParserDriver{
		protected $patten = '/\d*\.\d*\.\d*/';
		protected $path = 'local';
		protected $method = 'insert';
		protected $_messages = array(
			'common' => array(
				'element'	=>'apiVersion',
				'usage'		=>'@apiVersion major.minor.patch',
				'example'	=>'@apiDefine 1.2.3',
			)
		);
		
		public function __construct(){
			
		}
		
		public function parse($content,$source='',$message = ''){
			if(!$message)
				 $message = $this->_messages;
			$content = trim($content);
			
			if(empty($content))
				return null;
			if(!$this->checkVersion($content)){
				throw new Exception('Version format not valid.'.'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example']);
			}
			return array(
				'version'=>$content
			);
		}
		
		public function checkVersion($str){
			preg_match($this->patten,$str,$res);
			
			if(!$res)
				return ;
			
			return true;
		}
	}