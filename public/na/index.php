<?php

require '../template.php';

if(preg_match('#/na/(.+)$#', $_SERVER['REQUEST_URI'], $match)) {
	$country = (string)$match[1];
	$country = strtoupper($country);
} else {
	print <<<EOF
<table>
<thead>
	<tr>
		<th>App Store</th>
		<th>Number of unavailable apps</th>
	</tr>
</thead>
<tbody>
EOF;
	$mongodb_query = new MongoDB\Driver\Query([], ['sort' => ['apps_unavailable' => -1]]);
	foreach($mongodb_manager->executeQuery('ac.countries', $mongodb_query) as $country) {
		print '<tr>';
		print '<td>';
		print get_territory_name($country->_id);
		print '</td>';
		print '<td>';
		print '<a href="/na/' . $country->_id . '">';
		print $country->apps_unavailable;
		print '</a>';
		print '</td>';
		print '</tr>';
		print "\n";
	}
	print <<<EOF
</tbody>
</table>
EOF;
	exit;
}

$mongodb_query = new MongoDB\Driver\Query(['_id' => $country]);
$record = $mongodb_manager->executeQuery('ac.countries', $mongodb_query)->toArray();
if(!$record) {
	h404();
}

$mongodb_query = new MongoDB\Driver\Query([
	'country' => $country,
	'available' => false
]);
$rows = $mongodb_manager->executeQuery('ac.last_statuses', $mongodb_query)->toArray();
foreach ($rows as $row) {
	$row->app = get_app($row->id);
}

function app_weight($app) {
	if($app->ranking) {
		return $app->ranking;
	}
	return 999999;
}

usort($rows, function($a, $b) {
	return app_weight($a->app) - app_weight($b->app);
});
print '<table>';
print '<tbody>';
print "\n";
foreach ($rows as $row) {
	print '<tr>';
	print '<td>';
	print '<a href="/app/' . $row->id . '">';
	print $row->id;
	print '</a>';
	print '</td>';
	print '<td class="na">';
	print '<a href="' . get_itunes_url($country, $row->app->name, $row->id) . '" target="_blank">';
	print $row->app->name;
	print '</a>';
	print '</td>';
	print '<td>';
	$genre = get_app_genre($row->id, 'US');
	if(!$genre) {
		$genre = get_app_genre($row->id);
	}
	print $genre;
	print '</td>';
	print '</tr>';
	print "\n";
}
print '</tbody>';
print '</table>';
