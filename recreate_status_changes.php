<?php

require 'inc/main.inc';

const MIN_DURATION = 60 * 60 * 24;
const MIN_CONFIRMATIONS = 5;

$mongodb_query = new MongoDB\Driver\Query([], ['sort' => ['ts' => 1]]);
$rows = $mongodb_manager->executeQuery('ac.statuses', $mongodb_query);
$apps = [];
foreach($rows as $row) {
	if(!isset($apps[$row->id])) {
		$apps[$row->id] = ['t' => []];
	}
	if(!isset($apps[$row->id]['t'][$row->territory])) {
		$apps[$row->id]['t'][$row->territory] = [];
	}
	$apps[$row->id]['t'][$row->territory][$row->ts] = $row->available;
}

global $mongodb_manager;
$mongodb_bulk = new MongoDB\Driver\BulkWrite();

function insertChangeIfSignificant($change) {
	if(!$change) {
		return false;
	}

	if(!isset($change->duration)) {
		return false;
	}

	if($change->duration < MIN_DURATION) {
		return false;
	}

	if($change->confirmations < MIN_CONFIRMATIONS) {
		return false;
	}

	if($change->change == -1) {
		$margin = 60 * 60 * 24 * 2;
		if(!isAppAvailableSomewhereDuring($change->id, $change->ts + $margin, $change->ts + $change->duration)) {
			return false;
		}
	}

	global $mongodb_bulk;
	$mongodb_bulk->insert($change);
	return true;
}

function isAppAvailableSomewhereDuring($id, $ts_start, $ts_end) {
	global $apps;
	foreach($apps[$id]['t'] as $statuses) {
		foreach($statuses as $ts => $status) {
			if($ts < $ts_start) {
				continue;
			}
			if($ts > $ts_end) {
				break;
			}
			if($status) {
				return true;
			}
		}
	}
	return false;
}

foreach($apps as $id => $app) {
	foreach($app['t'] as $territory => $statuses) {
		$change = null;
		$last = new stdClass;
		foreach($statuses as $ts => $status) {
			if(isset($last->status)) {
				if($last->status == $status) {
					if($change) {
						$change->confirmations++;
						$change->duration = $ts - $change->ts;
					}
				} else {
					if(!$change || insertChangeIfSignificant($change)) {
						$change = new stdClass;
						$change->id = $id;
						$change->territory = $territory;
						$change->change = $status ? 1 : -1;
						$change->ts = $ts;
						$change->confirmations = 0;
					} else {
						$change = null;
					}
				}
			}
			$last->status = $status;
			$last->ts = $ts;
		}

		insertChangeIfSignificant($change);
	}
}

$mongodb_manager->executeBulkWrite('ac.status_changes_tmp', $mongodb_bulk);
