<?php
	namespace apidoc\Filter;
	use apidoc\Filter\apiparam;
	class apisuccess extends apiparam{
		public function postFilter($parsedFiles,$filenames,$tagName = 'success'){
			parent::postFilter($parsedFiles,$filenames,$tagName);
		}
	}