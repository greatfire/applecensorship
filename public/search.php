<?php
require '../inc/main.inc';

$term = trim($_GET['term']);
if(!$term) {
	exit;
}

$mongodb_query = new MongoDB\Driver\Query([
	'name' => [
		'$regex' => $term,
		'$options' => 'i'
	]
], [
	'limit' => 10,
	'sort' => [
		'userRatingCount' => -1
	]
]);
$matches = [];
foreach($mongodb_manager->executeQuery('ac.app_names', $mongodb_query) as $record) {
	$matches[$record->_id->id] = $record->name;
}

if(count($matches) > 5) {
	$matches = [];
}

header('Content-Type: application/json');
print json_encode($matches);
