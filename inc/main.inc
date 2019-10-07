<?php

const ITUNES_API_URL='https://itunes.apple.com/search';

const VALID_REQUEST_KEYS = [
	'country',
	'limit',
	'media',
	'term'
];

// Needs to define the following constants: MONGODB_COLLECTION, MONGODB_CONNECTION, PROXY
require 'local.inc';

require 'localization.inc';

$mongodb_manager = new MongoDB\Driver\Manager(MONGODB_CONNECTION);

function fatal($message)
{
	header('HTTP/1.1 500 Internal Server Error: ' . $message);
	exit();
}

function get_app_genre($id, $territory = null) {
	global $mongodb_manager;
	$filter = [
		'_id.id' => $id
	];
	if($territory) {
		$filter['_id.territory'] = $territory;
	}
	$mongodb_query = new MongoDB\Driver\Query($filter);
	$rows = $mongodb_manager->executeQuery('ac.app_genres', $mongodb_query);
	foreach($rows as $row) {
		return $row->genre;
	}
}

function get_app($id) {
	global $mongodb_manager;
	$mongodb_query = new MongoDB\Driver\Query([
		'_id' => (int)$id
	]);
	$rows = $mongodb_manager->executeQuery('ac.apps', $mongodb_query)->toArray();
	if($rows) {
		return $rows[0];
	}
}

function get_app_name($id, $territory = null) {
	global $mongodb_manager;
	if($territory) {
		$mongodb_query = new MongoDB\Driver\Query([
			'_id.territory' => $territory,
			'_id.id' => $id
		]);
		$rows = $mongodb_manager->executeQuery('ac.app_names', $mongodb_query);
	} else {
		$mongodb_query = new MongoDB\Driver\Query([
			'_id' => $id
		]);
		$rows = $mongodb_manager->executeQuery('ac.apps', $mongodb_query);
	}
	foreach($rows as $row) {
		return $row->name;
	}
}

function get_itunes_url($territory, $name, $id) {
	return 'https://itunes.apple.com/' . strtolower($territory) . '/app/' . urlencode($name) . '/id' . $id;
}

function get_territories() {
	static $territories;
	if(!isset($territories)) {
		$territories = [];
		foreach(file(dirname(__DIR__) . '/territories.tsv', FILE_IGNORE_NEW_LINES) as $line) {
			list($code, $name) = explode("\t", $line);
			$territories[$code] = $name;
		}
	}
	return $territories;
}

function get_territory_name($code) {
	$territories = get_territories();
	if(!isset($territories[$code])) {
		return trigger_error('Unknown terrirotory: [' . $code . ']');
	}
	return $territories[$code];
}

function h404() {
	echo 'Page not found';
	http_response_code(404);
	exit;
}

function itunes_api_proxy_request($request) {
	$url = ITUNES_API_URL;
	$url .= '?' . http_build_query($request);

	for ($i = 0; $i < 99; $i ++) {
		$timeout = 10 + pow($i, 2);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_PROXY, PROXY);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$json = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($json);
		if(!$response) {
			continue;
		}

		if(!isset($response->resultCount)) {
			continue;
		}

		save_request_response($request, $response, 'server');

		return $json;
	}
}

function mongo_write($data) {
	global $mongodb_manager;
        $mongodb_bulk = new MongoDB\Driver\BulkWrite();
        $mongodb_bulk->insert($data);
	$mongodb_manager->executeBulkWrite(MONGODB_COLLECTION, $mongodb_bulk);
}

function save_request_response($request, $response, $source) {
	$data = new stdClass();
	$data->request = $request;
	$data->response = $response;
	$data->source = $source;
	$data->ts = time();
	mongo_write($data);
}
