<?php

require '../template.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if(preg_match('#/na/(.+)$#', $path, $match)) {
	$territory = (string)$match[1];
	$territory = strtoupper($territory);
	require 'territory.inc';
} else {
	require 'territories.inc';
}
