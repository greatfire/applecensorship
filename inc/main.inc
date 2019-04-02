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

$mongodb_manager = new MongoDB\Driver\Manager(MONGODB_CONNECTION);

function fatal($message)
{
	header('HTTP/1.1 500 Internal Server Error: ' . $message);
	exit();
}

function get_app_genre($id, $country = null) {
	global $mongodb_manager;
	$filter = [
		'_id.id' => $id
	];
	if($country) {
		$filter['_id.country'] = $country;
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
		'_id' => $id
	]);
	$rows = $mongodb_manager->executeQuery('ac.apps', $mongodb_query)->toArray();
	if($rows) {
		return $rows[0];
	}
}

function get_app_name($id, $country = null) {
	global $mongodb_manager;
	if($country) {
		$mongodb_query = new MongoDB\Driver\Query([
			'_id.country' => $country,
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

function get_itunes_url($country, $name, $id) {
	return 'https://itunes.apple.com/' . strtolower($country) . '/app/' . urlencode($name) . '/id' . $id;
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
	return $territories[$code];
}

function h404() {
	echo 'Page not found';
	http_response_code(404);
	exit;
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
