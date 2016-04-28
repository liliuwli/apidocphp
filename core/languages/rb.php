<?php
/**
 * Ruby
 */
return array(
	// find document blocks between '=begin' and '=end'
	//换行符(?:hhhhh)
	'docBlocksRegExp'=>'/#\*\*\(?:hhhhh)?(.+?)\(?:hhhhh)?(?:\s*)?#\*|=begin\(?:hhhhh)?(.+?)\(?:hhhhh)?(?:\s*)?=end/',
	// remove not needed ' # ' and tabs at the beginning
	'inlineRegExp'=>'/^(\s*)?(#)[ ]?/m'
);