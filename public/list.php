<?php
require 'template.php';

$territory = 'CN';
if(isset($_GET['c'])) {
	$territory = (string)$_GET['c'];
}

$mongodb_query = new MongoDB\Driver\Query(['_id' => $territory]);
$record = $mongodb_manager->executeQuery('ac.territories', $mongodb_query)->toArray();
if(!$record) {
	echo 'Page not found';
	http_response_code(404);
	exit;
}

$filter = [
	'territory' => $territory,
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
