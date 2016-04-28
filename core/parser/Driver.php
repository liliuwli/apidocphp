<?php
	namespace apidoc\Parser;
	interface interfaceDriver{
		function __construct();
		function parse($content);
		function getPath();
		function getMethod();
	}