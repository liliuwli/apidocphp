<?php
	namespace apidoc\Filter;
	use apidoc\Filter\apiparam;
	class apiheader extends apiparam{
		public function postFilter($parsedFiles,$filenames,$tagName = 'header'){
			parent::postFilter($parsedFiles,$filenames,$tagName);
		}
	}