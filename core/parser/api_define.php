<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apidefine extends ParserDriver{
		protected $patten = '/^(\w*)(.*?)(?:\s+|$)(.*)$/m';
		protected $path = 'global.define';
		protected $method = 'insert';
		protected $markdownFields = array('description');
		protected $_messages = array(
			'common' => array(
				'element'	=>'apiDefine',
				'usage'		=>'@apiDefine name',
				'example'	=>'@apiDefine MyValidName',
			)
		);
		
		public function parse($content,$source='',$message = ''){
			if(!$message)
				 $message = $this->_messages;
			 
			$content = trim($content);
			
			$res = preg_match($this->patten,$content,$matches);
			
			if(!$res){
				return null;
			}
			
			if($matches[0] === '')
				throw new Exception('No arguments found.'.'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example']);
			
			if($matches[2] !== '')
				throw new Exception('名称必须包含字母或数字字符'.'element:'.$message['common']['element'].'usage:'.$message['common']['usage'].'example:'.$message['common']['example']);
			
			$name  = $matches[1];
			$title = $matches[3];
			
			$description = '';
			
			preg_match_all($this->patten,$content,$matches,PREG_SET_ORDER);
			
			foreach($matches as $match){
				$description .= $match[0];
			}
			
			return array(
				'name'			=>$name,
				'title'			=>$title,
				'description'	=>ltrim($description),
			);
		}
	}