<?php
/**
 * Erlang
 */
return array(
	// Find document blocks between '%{' and '%}'
	//换行符(?:hhhhh)
	'docBlocksRegExp'=>'/\%*\{(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?\%+\}/',
	// remove not needed ' % ' and tabs at the beginning
	// HINT: Not sure if erlang developer use the %, but i think it should be no problem
	'inlineRegExp'=>'/^(\s*)?(\%*)[ ]?/m'
);