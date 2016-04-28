<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apierrorexample extends ParserDriver{
		protected $patten = '/(@\w*)?(?:(?:\s*\{\s*([a-zA-Z0-9\.\/\\\[\]_-]+)\s*\}\s*)?\s*(.*)?)?/';
		protected $path = 'local.error.examples';
		protected $method = 'push';
		
		public function parse($content,$source=''){
			$source = trim($source);
			
			$title = '';
			$text = '';
			$type;
			
			// Search for @apiExample "[{type}] title and content
			// /^(@\w*)?\s?(?:(?:\{(.+?)\})\s*)?(.*)$/gm;
			$parseRegExpFollowing = '/(^.*\s?)/m';
			preg_match($this->patten,$source,$matches);
			if (!empty($matches)){
				$type  = $matches[2];
				$title = $matches[3];
			}
			
			preg_match_all($parseRegExpFollowing,$source,$matches,PREG_SET_ORDER);
			
			foreach($matches as $k=>$match){
				// ignore line 1
				if($k==0){
					continue;
				}
				$text .= $match[1];
			}
			
			if(empty($text))
				return null;
			
			return array(
				'title'			=>$title,
				'content'		=>ltrim($text),
				'type'			=>empty($type)?'json':$type
			);
		}
	}