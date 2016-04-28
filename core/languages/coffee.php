<?php
/**
 * CoffeeScript
 */
return array(
	// find document blocks between '###' and '###'
	//换行符(?:hhhhh)
	'docBlocksRegExp'=>'/###(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?###/',
	// remove not needed tabs at the beginning
	'inlineRegExp'=>'/^(\t*)?/m'
);