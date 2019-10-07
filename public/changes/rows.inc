<?php

$mongodb_query = new MongoDB\Driver\Query([], [
	'sort' => ['ts' => -1]
]);
$rows = $mongodb_manager->executeQuery('ac.status_changes', $mongodb_query)->toArray();
foreach($rows as $k => $row) {
	$row->app = get_app($row->id);
	$row->tsd = date('Y-m-d H:i', $row->ts);
	$row->app->last_available_tsd = date('Y-m-d H:i', $row->app->last_available_ts);
	$row->changeString = $row->change == 1 ? 'Restored' : 'Deleted';
	$row->territoryName = get_territory_name($row->territory);
}

$rows = array_filter($rows, function($row) {
	if($row->app->userRatingCount < 10) {
		return false;
	}
	return true;
});
