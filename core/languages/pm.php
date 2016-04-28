<?php
/**
 * Perl
 */
return array(
	// find document blocks between '#**' and '#*'
    // or between '=pod' and '=cut'
	//换行符(?:hhhhh)
	'docBlocksRegExp'=>'/#\*\*(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?#\*|=pod(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?=cut/',
	// remove not needed ' # ' and tabs at the beginning
	'inlineRegExp'=>'/^(\s*)?(#)[ ]?/m'
);