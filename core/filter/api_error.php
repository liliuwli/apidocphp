<?php
	namespace apidoc\Filter;
	use apidoc\Filter\apiparam;
	class apierror extends apiparam{
		public function postFilter($parsedFiles,$filenames,$tagName = 'error'){
			parent::postFilter($parsedFiles,$filenames,$tagName);
		}
	}