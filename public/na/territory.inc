<?php
$mongodb_query = new MongoDB\Driver\Query(['_id' => $territory]);
$record = $mongodb_manager->executeQuery('ac.territories', $mongodb_query)->toArray();
if(!$record) {
	h404();
}

$filter = [
	'territory' => $territory,
	'last_available' => false
];
if(isset($_GET['test'])) {
	$filter['max_available'] = true;
}
$mongodb_query = new MongoDB\Driver\Query($filter);
$rows = $mongodb_manager->executeQuery('ac.agg_statuses', $mongodb_query)->toArray();
$rows2 = [];
$genres = [];
foreach ($rows as $row) {
	$row = get_app($row->id);

	if(!$row->available) {
		continue;
	}

	if($row->ranking == 0) {
		$row->ranking = '-';
	} else {
		$row->ranking = round($row->ranking, 1);
	}

	if($row->userRatingCount == 0) {
		$row->userRatingCount = '-';
	} else {
		$row->userRatingCount = round($row->userRatingCount);
	}

	$row->genre = get_app_genre($row->_id, 'US');
	if(!$row->genre) {
		$row->genre = get_app_genre($row->_id);
	}
	if($row->genre && !in_array($row->genre, $genres)) {
		$genres[] = $row->genre;
	}

	$rows2[] = $row;
}
$rows = $rows2;
sort($genres);
?>
<div id="app">
	<h3><?php pf('$1 apps are unavailable in the $2 App Store', count($rows), get_territory_name($territory)) ?></h3>
	<download-data :data="rows" filename="<?php print $territory ?>"></download-data>
	<table>
		<thead>
			<tr>
				<th></th>
				<th @click="sort('name')"><?php p('App name') ?></th>
				<th @click="sort('genre')"><?php p('Genre') ?></th>
				<th @click="sort('ranking')"><?php p('Average ranking') ?></th>
				<th @click="sort('userRatingCount')"><?php p('Number of user ratings') ?></th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th>
					<select v-model="genreFilter">
						<option value=""><?php p('All genres') ?></option>
						<option v-for="genre in genres">{{ genre }}</option>
					<select>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="row in rowsSortedFiltered">
				<td><img v-lazy="row.artwork" :key="row.artwork" height="50" width="50"></td>
				<td><a v-bind:href="'/app/' + row._id">{{ row.name }}</a></td>
				<td>{{ row.genre }}</td>
				<td>{{ row.ranking }}</td>
				<td>{{ row.userRatingCount }}</td>
			</tr>
		</tbody>
	</table>
</div>
<script>
Vue.use(VueLazyload);
new Vue({
	el: '#app',
	data: {
		genreFilter: null,
		genres: <?php print json_encode($genres) ?>,
		rows: <?php print json_encode($rows); ?>,
		sortBy: 'userRatingCount'
	},
	computed: {
		rowsSortedFiltered: function() {
			return this.rows.filter((row) => {
				if(this.genreFilter && this.genreFilter != row.genre) {
					return false;
				}
				return true;
			}).sort((a, b) => {
				const sortByColumns = [this.sortBy, 'ranking', 'userRatingCount', 'name', 'genre'];
				for(let s = 0; s < sortByColumns.length; s++) {
					let sortBy = sortByColumns[s];
					if(a[sortBy] != b[sortBy]) {
						if(a[sortBy] == '-') {
							return true;
						}
						if(b[sortBy] == '-') {
							return false;
						}
						if(sortBy == 'userRatingCount') {
							let c = b;
							b = a;
							a = c;
						}
						return a[sortBy] > b[sortBy];
					}
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
