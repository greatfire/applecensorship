<?php

require 'inc/main.inc';

function shuffle_assoc(&$array, $limit = null) {
	$keys = array_keys($array);
	shuffle($keys);
	$new = [];
	foreach($keys as $key) {
		$new[$key] = $array[$key];
		if($limit && count($new) >= $limit) {
			break;
		}
	}
	$array = $new;
	return true;
}

$mongodb_query = new MongoDB\Driver\Query([]);
$rows = $mongodb_manager->executeQuery('ac.territories', $mongodb_query);
$territories = [];
$general_territory_ids = [];
foreach($rows as $row) {
	// Always test US
	if($row->_id == 'US') {
		$general_territory_ids[] = $row->_id;
		continue;
	}

	$territories[] = $row;
}

// Territory with fewest tested apps
usort($territories, function($a, $b) {
	return $a->apps_count > $b->apps_count;
});
$territory = array_shift($territories);
$general_territory_ids[] = $territory->_id;

// Territory with most unavailable apps
usort($territories, function($a, $b) {
	return $a->apps_unavailable < $b->apps_unavailable;
});
$territory = array_shift($territories);
$general_territory_ids[] = $territory->_id;

// Random territory
shuffle($territories);
$territory = array_shift($territories);
$general_territory_ids[] = $territory->_id;

$ids = [];

// Add top apps 
$urls = [
	'https://rss.itunes.apple.com/api/v1/us/ios-apps/top-free/all/100/explicit.json',
	'https://rss.itunes.apple.com/api/v1/us/ios-apps/top-paid/all/100/explicit.json'
];
foreach($urls as $url) {
	$json = file_get_contents($url);
	$data = json_decode($json);
	foreach($data->feed->results as $result) {
		$app = get_app($result->id);
		if(!$app) {
			$ids[$result->id] = [];
		}
	}
}

// Add apps that haven't been tested in a long time
$mongodb_query = new MongoDB\Driver\Query([], [
	'sort' => [
		'ts' => 1
	],
	'limit' => 70
]);
$rows = $mongodb_manager->executeQuery('ac.last_statuses', $mongodb_query);
foreach($rows as $row) {
	if(!isset($ids[$row->id])) {
		$ids[$row->id] = [];
	}
	$ids[$row->id][] = $row->territory;
}

// Add apps with high dissonance (eg unclear status)
$mongodb_query = new MongoDB\Driver\Query([], [
	'sort' => [
		'dissonance' => -1
	],
	'limit' => 30
]);
$rows = $mongodb_manager->executeQuery('ac.avg_status_dissonance', $mongodb_query);
foreach($rows as $row) {
	if(!isset($ids[$row->id])) {
		$ids[$row->id] = [];
	}
	$ids[$row->id][] = $row->territory;
}

shuffle_assoc($ids, 100);

foreach($ids as $id => $territory_ids) {
	$territory_ids = array_merge($territory_ids, $general_territory_ids);
	$territory_ids = array_unique($territory_ids);
	foreach($territory_ids as $territory_id) {
		$request = [];
		$request['media'] = 'software';
		$request['country'] = $territory_id;
		$request['term'] = $id;
		print 'Requesting ID ' . $request['term'] . ' for territory ' . $request['country'] . '...';
		itunes_api_proxy_request($request);
		print " done\n";
	}
}
