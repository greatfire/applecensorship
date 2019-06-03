<?php
$mongodb_query = new MongoDB\Driver\Query(['_id' => $territory]);
$record = $mongodb_manager->executeQuery('ac.territories', $mongodb_query)->toArray();
if(!$record) {
	h404();
}

$mongodb_query = new MongoDB\Driver\Query([
	'territory' => $territory,
	'available' => false
]);
$rows = $mongodb_manager->executeQuery('ac.last_statuses', $mongodb_query)->toArray();
$genres = [];
foreach ($rows as &$row) {
	$row = get_app($row->id);
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
}
sort($genres);
?>
<div id="app">
	<h3>Apps unavailable in the <?php print get_territory_name($territory) ?> App Store</h3>
	<table>
		<thead>
			<tr>
				<th></th>
				<th @click="sort('name')">App name</th>
				<th @click="sort('genre')">Genre</th>
				<th @click="sort('userRatingCount')">Number of user ratings</th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th>
					<select v-model="genreFilter">
						<option value="">All genres</option>
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
				const sortByColumns = [this.sortBy, 'name', 'genre', 'userRatingCount'];
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
	}
});
</script>
