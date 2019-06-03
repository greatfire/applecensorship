<?php

require '../template.php';

if(preg_match('#/na/(.+)$#', $_SERVER['REQUEST_URI'], $match)) {
	$territory = (string)$match[1];
	$territory = strtoupper($territory);
	require 'territory.inc';
} else {
	require 'territories.inc';
}
