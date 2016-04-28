<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class apiexample extends ParserDriver{
		protected $patten = '/(@\w*)?(?:(?:\s*\{\s*([a-zA-Z0-9\.\/\\\[\]_-]+)\s*\}\s*)?\s*(.*)?)?/';
		protected $path = 'local.examples';
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
			$content = ltrim($text);
			if(preg_match('/3799:/',$content,$match)){
				$content = preg_replace('/3799:/','*',$content);
				$content = "/**\n".$content."\n*/";
			}
			return array(
				'title'			=>$title,
				'content'		=>$content,
				'type'			=>empty($type)?'json':$type
			);
		}
	}