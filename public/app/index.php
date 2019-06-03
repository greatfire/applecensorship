<?php

require '../../inc/main.inc';

if(preg_match('#/app/(\d+)(/([^/]+)/([^/]+))?$#', $_SERVER['REQUEST_URI'], $url_match)) {
	$id = (int)$url_match[1];
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
$groups = [];
$territories = [];
$details = [];
foreach($rows as $row) {
	if(!isset($row->territory)) {
		continue;
	}

	$t = date('Y-m-d', $row->ts);

	if(isset($url_match[4]) && $url_match[4] == $t) {
		if(isset($url_match[3]) && $url_match[3] == $row->territory) {
			$mongodb_query = new MongoDB\Driver\Query([
				'_id' => $row->main_id
			]);
			$main_rows = $mongodb_manager->executeQuery('ac.main', $mongodb_query)->toArray();
			$main_row = $main_rows[0];
			unset($main_row->_id);
			$details[] = $main_row;
			continue;
		}
	}

	$groups[$t][$row->territory][] = $row;
	if(!in_array($row->territory, $territories)) {
		$territories[] = $row->territory;
	}
}

if($details) {
	header('Content-Type: application/json');
	print json_encode($details, JSON_PRETTY_PRINT);
	exit;
}

sort($territories);

require '../template.php';

if(!$app) {
	print '<h3>' . $id . '</h3>';
	print '<p>No data found for this app. Perhaps it has not yet been added to our database. Please come back later or <a href="' . get_itunes_url('US', 'unknown', $id) . '" target="_blank">search the App Store</a>.';
	exit;
}
?>
	<h3><?php print $app->name ?></h3>
	<img src="<?php print $app->artwork ?>">
	<p>Number of user ratings: <?php print round($app->userRatingCount) ?></p>
	<table>
	<thead>
		<tr>
			<th></th>
			<?php
			foreach($territories as $territory) {
			?>
			<th><a href="/na/<?php print $territory ?>"><?php print get_territory_name($territory) ?></a></th>
			<?php
			}
		?>
		</tr>
		<tr>
			<th>Date</th>
			<?php
			foreach($territories as $territory) {
			?>
			<th>
				<?php 
				$app_name_territory = get_app_name($id, $territory);
				if(!$app_name_territory) {
					$app_name_territory = $app->name;
				}
				?>
				<a href="<?php print get_itunes_url($territory, $app_name_territory, $id) ?>" target="_blank"><?php print $app_name_territory ?></a>
			</th>
			<?php
			}
		?>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($groups as $t => $cs) {
	?>
		<tr>
			<td><?php print $t ?></td>
			<?php
			foreach($territories as $territory) {
				$td_class = '';
				$td_v = '';
				$a_ids = [];
				if(isset($cs[$territory])) {
					$pc = round(100 * array_reduce($cs[$territory], function($v, $w) {
						$v += $w->available;
						return $v;
					}) / count($cs[$territory]));
					if($pc == 100) {
						$td_class = 'a';
					} elseif($pc == 0) {
						$td_class = 'na';
					}
					$td_v = $pc . '%';
					foreach($cs[$territory] as $v) {
						$a_ids[] = $v->_id;
					}
				} else {
					$td_v = '-';
				}
				?>
			<td class="<?php print $td_class ?>"><a href="/app/<?php print $id ?>/<?php print $territory ?>/<?php print $t ?>"><?php print $td_v ?></a></td>
			<?php
			}
			?>
		</tr>
	<?php } ?>
	</tbody>
	</table>
