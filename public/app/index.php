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
$countries = [];
$details = [];
foreach($rows as $row) {
	$t = date('Y-m-d', $row->ts);

	if(isset($url_match[4]) && $url_match[4] == $t) {
		if(isset($url_match[3]) && $url_match[3] == $row->country) {
			$mongodb_query = new MongoDB\Driver\Query([
				'_id' => $row->main_id
			]);
			$main_rows = $mongodb_manager->executeQuery('ac.main', $mongodb_query)->toArray();
			if(!$main_rows) {
				print_r($main_rows);
				exit;
				continue;
			}
			$main_row = $main_rows[0];
			unset($main_row->_id);
			$details[] = $main_row;
			continue;
		}
	}

	$groups[$t][$row->country][] = $row;
	if(!in_array($row->country, $countries)) {
		$countries[] = $row->country;
	}
}

if($details) {
	header('Content-Type: application/json');
	print json_encode($details, JSON_PRETTY_PRINT);
	exit;
}

sort($countries);

require '../template.php';
?>
	<h1><?php print $app->name ?></h1>
	<p>Average ranking: <?php print round($app->ranking) ?></p>
	<table>
	<thead>
		<tr>
			<th></th>
			<?php
			foreach($countries as $country) {
			?>
			<th><?php print get_territory_name($country) ?></th>
			<?php
			}
		?>
		</tr>
		<tr>
			<th>Date</th>
			<?php
			foreach($countries as $country) {
			?>
			<th>
				<?php 
				$app_name_country = get_app_name($id, $country);
				if(!$app_name_country) {
					$app_name_country = $app->name;
				}
				?>
				<a href="<?php print get_itunes_url($country, $app_name_country, $id) ?>" target="_blank"><?php print $app_name_country ?></a>
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
			foreach($countries as $country) {
				$td_class = '';
				$td_v = '';
				$a_ids = [];
				if(isset($cs[$country])) {
					$pc = round(100 * array_reduce($cs[$country], function($v, $w) {
						$v += $w->available;
						return $v;
					}) / count($cs[$country]));
					if($pc == 100) {
						$td_class = 'a';
					} elseif($pc == 0) {
						$td_class = 'na';
					}
					$td_v = $pc . '%';
					foreach($cs[$country] as $v) {
						$a_ids[] = $v->_id;
					}
				} else {
					$td_v = '-';
				}
				?>
			<td class="<?php print $td_class ?>"><a href="/app/<?php print $id ?>/<?php print $country ?>/<?php print $t ?>"><?php print $td_v ?></a></td>
			<?php
			}
			?>
		</tr>
	<?php } ?>
	</tbody>
	</table>
