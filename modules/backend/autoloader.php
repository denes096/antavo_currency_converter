<?php

const PROJECT_BASE_DIRS = [
	'controller',
	'model',
];


foreach (PROJECT_BASE_DIRS as $dir) {
	$files = scandir(__DIR__ . DIRECTORY_SEPARATOR . $dir);

	if (!$files) {
		// log not a dir
	}

	foreach ($files as $file) {
		if ($file === '.' || $file === '..') {
			continue;
		}
		include_once __DIR__ . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
	}

}
