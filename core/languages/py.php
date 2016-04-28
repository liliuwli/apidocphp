<?php
/**
 * Python
 */
return array(
	// find document blocks between '#**' and '#*'
    // or between '=pod' and '=cut'
	//换行符(?:hhhhh)
	'docBlocksRegExp'=>'/\"\"\"(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?\"\"\"/',
	// remove not needed tabs at the beginning
	'inlineRegExp'=>'/^(\t*)?/m'
);