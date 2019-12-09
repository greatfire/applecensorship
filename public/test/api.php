<?php
require '../../inc/main.inc';

const CACHE_EXPIRY = 86400;

if (!isset($_GET['callback'])) {
	fatal('Missing callback string');
}
define('CALLBACK', $_GET['callback']);
unset($_GET['callback']);

$request = [];
foreach(VALID_REQUEST_KEYS as $key) {
	if (isset($_GET[$key])) {
		$request[$key] = $_GET[$key];
	}
}

function getJson() {
	global $mongodb_manager, $request;

	$filter = [
		'request' => $request,
		'source' => 'server',
		'ts' => [
			'$gt' => time() - CACHE_EXPIRY
		]
	];
	$options = [
		'limit' => 1,
		'sort' => [
			'ts' => - 1
		]
	];
	$mongodb_query = new MongoDB\Driver\Query($filter, $options);
	$rows = $mongodb_manager->executeQuery(MONGODB_COLLECTION, $mongodb_query);
	foreach ($rows as $row) {
		if (isset($row->response)) {
			$json = json_encode($row->response);
			return $json;
		}
	}

	return itunes_api_proxy_request($request);
}

$json = getJson();
if ($json) {
	print CALLBACK;
	print '(';
	print trim($json);
	print ');';
} else {
	fatal('empty response');
}
