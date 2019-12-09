<?php

require '../template.php';
require 'rows.inc';

$territories = [];
foreach($rows as $row) {
	if(!in_array($row->territoryName, $territories)) {
		$territories[] = $row->territoryName;
	}
}
$rows = array_values($rows);
sort($territories);
?>
<style>
#rss {
	float: right;
	width: 50px;
}
h3 {
	clear: right;
}
td {
	max-width: 300px;
}
</style>
<div id="app">
	<a href="rss.php"><img id="rss" src="/img/rss.svg"></a>
	<h3><?php pf('$1 detected changes', '{{ Object.keys(rowsFiltered).length }}') ?></h3>
	<table>
		<thead>
			<tr>
				<th><?php p('App') ?></th>
				<th><?php p('App Ratings') ?></th>
				<th><?php p('App Store') ?></th>
				<th><?php p('Time') ?></th>
				<th><?php p('Change') ?></th>
				<th><?php p('Confirmed duration') ?></th>
				<th><?php p('Confirmations') ?></th>
			</tr>
			<tr>
                                <th></th>
                                <th></th>
                                <th>
                                        <select v-model="territoryFilter">
                                                <option value=""><?php p('All App Stores') ?></option>
                                                <option v-for="territory in territories">{{ territory }}</option>
                                        <select>
                                </th>
                                <th></th>
                        </tr>
		</thead>
		<tbody>
			<tr v-for="row in rowsFiltered">
				<td><a v-bind:href="'/app/' + row.id">{{ row.app.name }}</a></td>
				<td>{{ Math.round(row.app.userRatingCount) }}</td>
				<td>{{ row.territoryName }}</td>
				<td>{{ row.tsd }}</td>
				<td>{{ row.changeString }}</td>
				<td>{{ row.duration }}</td>
				<td>{{ row.confirmations }}</td>
			</tr>
		</tbody>
	</table>
</div>
<script>
new Vue({
	el: '#app',
	data: {
		rows: <?php print json_encode($rows); ?>,
		territories: <?php print json_encode($territories); ?>,
		territoryFilter: ''
	},
	computed: {
                rowsFiltered: function() {
                        return this.rows.filter((row) => {
                                if(this.territoryFilter && this.territoryFilter != row.territoryName) {
                                        return false;
                                }
                                return true;
                        });
                }
        }
});
</script>
