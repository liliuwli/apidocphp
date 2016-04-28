<?php
	namespace apidoc\Parser;
	use apidoc\Parser\ParserDriver;
	class api extends ParserDriver{
		protected $patten = '/^(?:(?:\{(.+?)\})?\s*)?(.+?)(?:\s+(.+?))?$/';
		protected $path = 'local';
		protected $method = 'insert';
		public function __construct(){
			
		}
		
		public function parse($content){
			preg_match($this->patten,$content,$this->res);
			$res = array(
				'type' => $this->res[1],
				'url' => $this->replace($this->res[2]),
				'title' => !empty($this->res[3])?$this->res[3]:'',
			);
			return $res;
		}
		
		private function replace($content){
			$content = preg_replace('/&nbsp;/',' ',$content);
			$content = preg_replace('/<br>/',PHP_EOL."\t",$content);
			return $content;
		}
	}