<?php
require '../inc/main.inc';

function fatal($message) {
	header('HTTP/1.1 500 Internal Server Error: ' . $message);
	exit;
}

const CACHE_EXPIRY = 86400;

$url = ITUNES_API_URL;
if(!isset($_GET['callback'])) {
	fatal('Missing callback string');
}
define('CALLBACK', $_GET['callback']);
unset($_GET['callback']);
if(!isset($_SERVER['QUERY_STRING'])) {
	fatal('Missing query string');
}

$url .= '?' . http_build_query($_GET);

function getJson($url) {
	$cache_key = str_replace('/', '_', base64_encode($url));
	$cache_path = '../cache/' . $cache_key;
	if(is_file($cache_path)) {
		$cachemago = time() - filemtime($cache_path);
		if($cachemago < CACHE_EXPIRY) {
			return file_get_contents($cache_path);
			exit;
		}
	}

	for($i = 0; $i < 99; $i++) {
		$timeout = 10 + pow($i, 2);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_PROXY, PROXY);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$json = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($json);
		if(!$data) {
			continue;
		}

		if(!isset($data->resultCount)) {
			continue;
		}

		file_put_contents($cache_path, $json);
		return $json;
	}
}

$json = getJson($url);
if($json) {
	print CALLBACK;
	print '(';
	print trim($json);
	print ');';
} else {
	fatal('empty response');
}
