<?php
$mongodb_query = new MongoDB\Driver\Query([]);
$news_unavailable = [];
foreach($mongodb_manager->executeQuery('ac.territory_genres', $mongodb_query) as $row) {
	if($row->_id->genre == 'News') {
		$news_unavailable[$row->_id->territory] = $row->apps_unavailable;
	}
}

$mongodb_query = new MongoDB\Driver\Query([]);
$rows = [];
foreach($mongodb_manager->executeQuery('ac.territories', $mongodb_query) as $row) {
	$row->name = get_territory_name($row->_id);
	if(isset($news_unavailable[$row->_id])) {
		$row->news_unavailable = $news_unavailable[$row->_id];
	} else {
		$row->news_unavailable = 0;
	}
	$rows[] = $row;
}
?>
<div id="app">
	<table>
		<thead>
			<tr>
				<th @click="sort('name')"><?php p('App Store') ?></th>
				<th @click="sort('news_unavailable')"><?php p('Number of unavailable news apps') ?></th>
				<th @click="sort('apps_unavailable')"><?php p('Number of unavailable apps') ?></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="row in rowsSorted">
				<td><a v-bind:href="'/na/' + row._id">{{ row.name }}</a></td>
				<td>{{ row.news_unavailable }}</td>
				<td>{{ row.apps_unavailable }}</td>
			</tr>
		</tbody>
	</table>
</div>
<script>
new Vue({
	el: '#app',
	data: {
		rows: <?php print json_encode($rows); ?>,
		sortBy: 'news_unavailable'
	},
	computed: {
		rowsSorted: function() {
			return this.rows.sort((a, b) => {
				if(a[this.sortBy] != b[this.sortBy]) {
					if(this.sortBy == 'apps_unavailable' || this.sortBy == 'news_unavailable') {
						let c = b;
						b = a;
						a = c;
					}
					return a[this.sortBy] > b[this.sortBy];
				}
			});;
		}
	},
	methods: {
		sort: function(s) {
			this.sortBy = s;
		}
	},
	updated: function() {
		this.setL();
	}
});
</script>
