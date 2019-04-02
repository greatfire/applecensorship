<?php
require 'template.php';

$country = 'CN';
if(isset($_GET['c'])) {
	$country = (string)$_GET['c'];
}

$mongodb_query = new MongoDB\Driver\Query(['_id' => $country]);
$record = $mongodb_manager->executeQuery('ac.countries', $mongodb_query)->toArray();
if(!$record) {
	echo 'Page not found';
	http_response_code(404);
	exit;
}

$filter = [
	'country' => $country,
	'available' => false
];
$mongodb_query = new MongoDB\Driver\Query($filter);
$rows = $mongodb_manager->executeQuery('ac.last_statuses', $mongodb_query);
print '<table>';
print '<tbody>';
print "\n";
foreach ($rows as $row) {
	print '<tr>';
	print '<td>';
	print '<a href="app/' . $row->id . '">';
	print $row->id;
	print '</a>';
	print '</td>';
	print '<td>';
	$name = get_app_name($row->id);
	print $name;
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
