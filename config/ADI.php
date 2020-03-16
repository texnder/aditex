<?php

return [

	/**
	 |----------------------------------------------------------
	 | set full interface mapper file path to resolve interface
	 |----------------------------------------------------------
	 | using mapper file aditex InterfaceResolver
	 | resolves insterface name with there respective class name
	 */

	"mapper" => __dir__."/mapper.php",

	/**
	 |-----------------------------------------------
	 | set bootex psr4 namespace 
	 |-----------------------------------------------
	 | To make directory files available in aditex,
	 | bootex service class starts scaning full directory
	 | so that, it can inject required dependencies
	 | in constructor or method of calling class...
	 | we can also skip specifying psr4 array here, 
	 | and can be register in bootex default psr4 file,
	 | or we can specify file path which returns psr4 array..
	 */

	 "psr4" => [

	 		"aditex\\src" => __DIR__."/../src"

	 ]
];