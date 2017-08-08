<?php

/*
 *
 * WorldEditArt
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

if(version_compare(PHP_VERSION, "7.0.0", "<")){
	echo "Fatal: This entry script requires PHP >=7.0.0!\n";
	exit;
}

if(!defined("STDIN")){
	define("STDIN", fopen("php://stdin", "rb"));
}

spl_autoload_register(function (string $class){
	$holder = Phar::running() . "/entry/";
	$file = $holder . str_replace("\\", "/", $class) . ".php";
	if(is_file($file)){
		require_once $file;
	}else{
		throw new RuntimeException("Class $class not found!");
	}
});

exit(0);
