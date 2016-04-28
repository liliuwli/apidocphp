<?php
/**
 * Erlang
 */
return array(
	// Find document blocks in heredocs that are arguments of the @apidoc
    // module attribute. Elixir heredocs can be enclosed between """ and """ or
    // between ''' and '''. Heredocs in ~s and ~S sigils are also supported.
	//换行符(?:hhhhh)
	'docBlocksRegExp'=>'/@apidoc\s*(~[sS])?"""(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?"""|@apidoc\s*(~[sS])?\'\'\'(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?\'\'\'/',
	// Remove not needed tabs at the beginning
	'inlineRegExp'=>'/^(\t*)?/m'
);