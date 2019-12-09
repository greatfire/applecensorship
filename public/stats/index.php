<?php

require '../template.php';

$months = [];

$collections = [
	'ac.apps' => ['first_ts', []],
	'ac.numeric_requests' => ['ts', []],
	'ac.status_changes' => ['ts', ['change' => -1]]
];
foreach($collections as $collection => $values) {
	list($key, $filter) = $values;
	$rows = $mongodb_manager->executeQuery($collection, new MongoDB\Driver\Query($filter));
	foreach($rows as $row) {
		if(!$row->$key) {
			continue;
		}
		$month = date('Y-m', $row->$key);
		if(!isset($months[$month])) {
			$months[$month] = array_fill_keys(array_keys($collections), 0);
		}
		$months[$month][$collection]++;
	}
}

ksort($months);
?>
<table>
	<thead>
		<tr>
			<th>Month</th>
			<th>Unique number of new apps added for testing</th>
			<th>Number of tests</th>
			<th>Number of unique apps detected as unavailable for the first time</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($months as $month => $stats) { ?>
		<tr>
			<td><?php print $month ?></td>
			<td><?php print number_format($stats['ac.apps']) ?></td>
			<td><?php print number_format($stats['ac.numeric_requests']) ?></td>
			<td><?php print number_format($stats['ac.status_changes']) ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
