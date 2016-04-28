<?php
/**
 * Clojure
 */
	return array(
		'docBlocksRegExp'=>'/\;{4}(?:hhhhh)?(.+?)(?:hhhhh)?(?:\s*)?;{4}/',
		//换行符(?:hhhhh)
		'inlineRegExp'=>'/^(\s*)?(;{2})[ ]?/m',
		// remove not needed ' ;; ' at the beginning
	);