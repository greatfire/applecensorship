<?php

require '../../inc/main.inc';

if(isset($_SERVER['REDIRECT_ID'])) {
	$id = (int)$_SERVER['REDIRECT_ID'];
} else {
	http_response_code(404);
	exit;
}
$app = get_app($id);

$mongodb_query = new MongoDB\Driver\Query([
	'id' => $id
], [
	'sort' => ['ts' => -1]
]);
$rows = $mongodb_manager->executeQuery('ac.statuses', $mongodb_query);
$dates = [];
$territory_agg_statuses = [];
foreach($rows as $row) {
	if(!isset($row->territory)) {
		continue;
	}

	$t = date('Y-m-d', $row->ts);

	if(isset($_SERVER['REDIRECT_TERRITORY']) && $_SERVER['REDIRECT_TERRITORY'] == $row->territory) {
		if(isset($_SERVER['REDIRECT_DATE']) && $_SERVER['REDIRECT_DATE'] == $t) {
			$mongodb_query = new MongoDB\Driver\Query([
				'_id' => $row->main_id
			]);
			$main_rows = $mongodb_manager->executeQuery('ac.main', $mongodb_query)->toArray();
			$details[] = $main_rows[0];
		}
	}

	$dates[$t][$row->territory][] = $row;

	if(!isset($territory_agg_statuses[$row->territory])) {
		$territory_agg_statuses[$row->territory] = [];
	}
	$territory_agg_statuses[$row->territory][] = $row->available;
}

if(isset($details)) {
	header('Content-Type: application/json');
	print json_encode($details, JSON_PRETTY_PRINT);
	exit;
}

foreach($territory_agg_statuses as $territory => &$v) {
	$last = $v[0];
	$v = array_sum($v) / count($v);
	if($v == 1) {
		$v = 3;
	} elseif($v == 0) {
	} else {
		$v = $last ? 2 : 1;
	}
}

$territory_groups = [];
foreach($territory_agg_statuses as $territory => $status) {
	if(!isset($territory_groups[$status])) {
		$territory_groups[$status] = [];
	}
	$territory_groups[$status][] = $territory;
}
ksort($territory_groups);
foreach($territory_groups as &$territory_group) {
	$territory_group = array_combine($territory_group, array_map(function($territory) {
		return get_territory_name($territory);
	}, $territory_group));
	asort($territory_group);
}

foreach($dates as $date => $date_territories) {
	$date_territory_groups = [];
	foreach($territory_groups as $status => $territories) {
		foreach($territories as $territory => $territory_name) {
			if(!isset($date_territories[$territory])) {
				continue;
			}
			if(!isset($date_territory_groups[$status])) {
				$date_territory_groups[$status] = [];
			}
			$date_territory_groups[$status][$territory] = round(100 * array_reduce($date_territories[$territory], function($v, $status) {
				return $v + $status->available;
			}) / count($date_territories[$territory]));
		}
	}
	$dates[$date] = $date_territory_groups;
}

/*
foreach($groups as $t => $cs) {
	print "$t\n";
	$new_groups = [];
	foreach($territory_groups as $territory_group) {
		$new_subgroups = [];
		foreach($territory_group as $territory) {
			$pc = null;
			if(isset($cs[$territory])) {
				$pc = array_reduce($cs[$territory], function($v, $w) {
					$v += $w->available;
					return $v;
				}) / count($cs[$territory]);
				if($pc != 0 && $pc != 1) {
					$pc = .5;
				}
			}
			if(!isset($new_subgroups[$pc])) {
				$new_subgroups[$pc] = [];
			}
			$new_subgroups[$pc][] = $territory;
		}
		if(isset($new_subgroups[null]) && count($new_subgroups) > 1) {
			$unknown = $new_subgroups[null];
			unset($new_subgroups[null]);
			uasort($new_subgroups, function($a, $b) {
				return count($a) < count($b);
			});
			$most_common_key = current(array_keys($new_subgroups));
			$new_subgroups[$most_common_key] = array_merge($new_subgroups[$most_common_key], $unknown);
		}
		$new_groups = array_merge($new_groups, $new_subgroups);
		if(count($new_groups) > 1) {
			print "New groups\n";
			print_r($new_groups);
		}
	}
	$territory_groups = $new_groups;
}
print_r($territory_groups);
exit;
*/

require '../template.php';

$term = $app->name;

function getStatusMessage($status) {
	switch((string)$status) {
		case '0':
			return 'Never Available';
		case '1':
			return 'Currently Not Available But Previously Available';
		case '2':
			return 'Currently Available But Previously Not Available';
		case '3':
			return 'Always Available';
	}
}

if(!$app) {
	print '<h3>' . $id . '</h3>';
	print '<p>' . t('No data found for this app') . '</p>';
	exit;
}
?>
<style>
table {
	border-collapse: collapse;
	table-layout:fixed;
}

table.groups-1 {
	max-width: 600px;
}
table.groups-1 th {
	width: 50%;
}

table.groups-2 {
	max-width: 700px;
}
table.groups-2 th {
	width: 40%;
}
table.groups-2 th:nth-child(1) {
	width: 20%;
}

table.groups-3 {
	max-width: 1000px;
}
table.groups-3 th {
	width: 28%;
}
table.groups-3 th:nth-child(1) {
	width: 16%;
}

table.groups-4 {
	max-width: 1300px;
}
table.groups-4 th {
	width: 22.5%;
}
table.groups-4 th:nth-child(1) {
	width: 10%;
}

th div {
	max-width: 200px;
}
th select {
	max-width: 18vw;
}

thead th {
	border: thin solid #ddd;
	padding: 0.8vw;
}
th.status-0 {
	background: red;
}
th.status-1 {
	background: yellow;
}
th.status-2 {
	background: yellow;
}
th.status-3 {
	background: green;
}

tbody th, td {
	padding: 0.4vw;
	word-break: break-all;
}
td div div {
	border: 2px solid yellow;
	border-radius: 5%;
	margin-top: 2%;
	padding: 2%;
}
td div div:nth-child(1) {
	margin: 0;
}

td div.collapse div {
	display: none;
}

td div.collapse div:nth-child(1),
td div.collapse div:nth-child(2),
td div.collapse div:nth-child(3) {
	display: block;
}
td div:not(.collapse) button {
	display: none;
}

td div.status-0 {
	border-color: red;
}
td div.status-100 {
	border-color: green;
}
td a {
	text-decoration: none;
}
</style>
<h3><?php print $app->name ?></h3>
<p><img src="<?php print $app->artwork ?>"></p>
<p><?php pf('Average ranking: $1', round($app->ranking)) ?></p>
<p><?php pf('Number of user ratings: $1', round($app->userRatingCount)) ?></p>
<h4><?php p('Availability') ?></h4>
<table id="app" class="groups-<?php print count($territory_groups) ?>">
<thead>
	<tr>
		<th></th>
		<?php
		foreach($territory_groups as $status => $g_territories) {
		?>
		<th class="status-<?php print $status ?>"><div><?php print t(getStatusMessage($status)) ?></div></th>
		<?php
		}
	?>
	</tr>
	<tr>
		<th></th>
		<th v-for="(territory_group, status) in territory_groups">
			<select v-if="Object.keys(territory_group).length > 1" v-model="selected_territories[status]">
				<option value="">{{ Object.keys(territory_group).length }} App Stores</option>
				<option v-for="(territory_name, territory) in territory_group" v-bind:value="territory">{{ territory_name }}</option>
			</select>
			<template v-else>{{ territory_group[Object.keys(territory_group)[0]] }}</template>
		</th>
	</tr>
	<tr>
		<th></th>
		<th v-for="(territory_group, status) in territory_groups">
			<a v-if="Object.keys(territory_group).length == 1" v-bind:href="'https://itunes.apple.com/' + Object.keys(territory_group)[0].toLowerCase() + '/app/<?php print $app->name ?>/id<?php print $app->_id ?>'" target="_blank">View in App Store</a>
			<a v-else-if="selected_territories[status]" v-bind:href="'https://itunes.apple.com/' + selected_territories[status].toLowerCase() + '/app/<?php print $app->name ?>/id<?php print $app->_id ?>'" target="_blank">View in App Store</a>
		</th>
	</tr>
</thead>
<tbody>
	<tr v-for="(tg, date) in dates">
		<th>{{ date.replace('-', '&#8203;-&#8203;') }}</th>
		<td v-for="(territories, status) in territory_groups_filtered">
			<div v-if="tg[status]" v-bind:class="{collapse: Object.keys(tg[status]).length > 3}">
				<div v-for="(territory_name, territory) in territories" v-bind:class="'status-' + tg[status][territory]" v-if="tg[status][territory] != null">
					<a v-bind:href="'/app/<?php print $app->_id ?>/' + territory + '/' + date">{{ territory_name }}</a>
				</div>
				<button v-if="!selected_territories[status]" onclick="this.parentNode.setAttribute('class', '')">More</button>
			</div>
		</td>
	</tr>
</tbody>
</table>
<script>
new Vue({
	el: '#app',
	data: {
		dates: <?php print json_encode($dates); ?>,
		selected_territories: <?php print json_encode(array_fill_keys(array_keys($territory_groups), '')); ?>,
		territory_groups: <?php print json_encode($territory_groups); ?>
	},
	computed: {
		territory_groups_filtered: function() {
			let territory_groups_filtered = {};
			for(let status in this.territory_groups) {
				territory_groups_filtered[status] = {};
				for(let territory in this.territory_groups[status]) {
					if(this.selected_territories[status]) {
						if(this.selected_territories[status] != territory) {
							continue;
						}
					}
					territory_groups_filtered[status][territory] = this.territory_groups[status][territory];
				}
			}
			return territory_groups_filtered;
		}
	},
	updated: function() {
		this.setL();
	}
});
</script>
