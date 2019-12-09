<?php
require 'template.php';

$mongodb_query = new MongoDB\Driver\Query([], [
	'limit' => 10
]);
$searches = [];
$rows = $mongodb_manager->executeQuery('ac.searches', $mongodb_query);
foreach ($rows as $row) {
	$searches[] = trim($row->_id);
}
$searches = array_unique($searches);
?>
<div>
	<div id="searches">
		<h3><?php p('Popular searches') ?></h3>
		<ol>
		<?php foreach($searches as $search) { ?>
			<li><a href="/test/<?php print urlencode($search) ?>"><?php print $search ?></a></li>
		<?php } ?>
		</ol>
	</div>
</div>
