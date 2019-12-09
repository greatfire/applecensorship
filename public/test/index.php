<?php
require '../template.php';
$term = urldecode($_SERVER['REDIRECT_TERM']);
?>
	<div id="app">
		<table>
			<thead>
				<th :style="{ width: columnWidth }" v-for="territory in territoriesActive" @click="sortAppsBy">{{ territory.name }}</th>
				<th :style="{ width: columnWidth }">
					<select @change="addTerritoryFromSelect" v-model="selectedTerritoryIndex">
						<option disabled value=""></option>
						<option v-for="(territory, territoryIndex) in territoriesInactive" :value="territoryIndex">{{ territory.name }}</option>
					</select>
				</th>
			</thead>
			<tbody v-if="(loading || apps.length > 0) && term.length > 0">
				<tr v-for="app in apps">
					<td v-for="appTerritory in app.territories" v-bind:class="{ a: appTerritory.available === true, na: appTerritory.available === false }">
						<span v-if="appTerritory.available === true">
							<a :href="'/app/' + app.id">{{ appTerritory.name }}</a><span v-if="appTerritory.ranking"> ({{ appTerritory.ranking }})</span>
						</span>
						<a v-if="appTerritory.available === false" :href="'/app/' + app.id">N/A</a>
						<img v-if="appTerritory.available === null" src="/img/ajax-loader.gif">
						<span v-if="appTerritory.available === -1">error</span>
					</td>
				</tr>
				<tr>
					<td v-for="territory in territoriesActive">
						<button v-if="loading === false" @click="searchTerritory(territory)">+</button>
						<img v-if="loading === true" src="/img/ajax-loader.gif">
					</td>
				</tr>
			</tbody>
		</table>
		<div v-if="!loading && apps.length == 0 && term.length > 0">No matches</div>
	</div>
	<script>
	var api_urls = ['<?php print ITUNES_API_URL ?>', 'api.php'];
	var search_limit_init = 10;
	var search_limit = search_limit_init;
	var search_to;

	function jsonp(params, then, fail, urls_remaining) {
		var save = false;
		if(urls_remaining == undefined) {
			save = true;
			urls_remaining = api_urls.slice();
		} else if(urls_remaining.length == 0) {
			console.error('No remaining urls', params);
			fail();
			return;
		}
		var url = urls_remaining.shift();
		return m.jsonp(url, {
			data: params
		}).then(function(response) {
			if(save) {
				save_data = params;
				save_data.response = JSON.stringify(response);
				post('save.php', save_data, function() {
				});
			}
			then(response);
		}).catch(function(err) {
			jsonp(params, then, fail, urls_remaining);
		});
	}

	function post(url, data, success) {
		var params = typeof data == 'string' ? data : Object.keys(data).map(
			function(k) {
				return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
			}
		).join('&');

		var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		xhr.open('POST', url);
		xhr.onreadystatechange = function() {
			if(xhr.readyState > 3 && xhr.status == 200) {
				success(xhr.responseText);
				ga('send', 'event', 'post', md5(params));
			}
		};
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(params);
		return xhr;
	}

	new Vue({
		el: '#app',
		data: {
			apps: [],
			loading: false,
			selectedTerritoryIndex: null,
			sortAppsByTerritoryIndex: 0,
			term: '<?php print $term ?>',
			territoriesActive: [],
			territoriesInactive: <?php
			$territories = [];
			foreach(file('../../territories.tsv', FILE_IGNORE_NEW_LINES) as $line) {
				list($code, $name) = explode("\t", $line);
				$territory = new stdClass;
				$territory->code = $code;
				$territory->name = $name;
				$territory->active = false;
				$territories[] = $territory;
			}
			usort($territories, function($a, $b) {
				return $a->name > $b->name;
			});
			print json_encode($territories);
			?>
		},
		computed: {
			columnWidth: function() {
				return Math.round(100 / (this.territoriesActive.length + 1)) + '%';
			},
		},
		methods: {
			addTerritoryFromCode(code) {
				var t = this.territoriesInactive.findIndex(t => t.code == code);
				this.addTerritoryFromT(t);
			},
			addTerritoryFromSelect() {
				this.addTerritoryFromT(this.selectedTerritoryIndex);
				this.selectedTerritoryIndex = null;
				this.supplementAppTerritories();
			},
			addTerritoryFromT(t) {
				var territory = this.territoriesInactive[t];
				this.territoriesActive.push(this.territoriesInactive[t]);
				this.territoriesInactive.splice(t, 1);
				for(var a = 0; a < this.apps.length; a++) {
					var app = this.apps[a];
					this.addTerritoryToApp(app, territory);
				}
			},
			addTerritoryToApp(app, territory) {
				app.territories.push({
					territory: territory,
					available: null,
					name: null,
					ranking: null
				});
			},
			appWeightTerritory(app, territoryIndex) {
				var appTerritory = app.territories[territoryIndex];
				if(appTerritory.ranking !== null) {
					return -1 / appTerritory.ranking;
				}
				for(var t = 0; t < app.territories.length; t++) {
					if(app.territories[t].ranking !== null) {
						return app.territories[t].ranking;
					}
				}
			},
			appWeightDefault(app) {
				var ranking_total = 0;
				var weight = 0;
				for(var t = 0; t < app.territories.length; t++) {
					if(app.territories[t].name === null) {
						continue;
					}
					if(app.territories[t].name.toLowerCase() == this.term.toLowerCase()) {
						weight = -4;
					}
					else if(app.territories[t].name.toLowerCase().indexOf(this.term.toLowerCase()) !== -1) {
						weight = Math.min(weight, -2);
					}

					if(app.territories[t].ranking) {
						ranking_total += app.territories[t].ranking;
					} else {
						ranking_total += (search_limit * 2);
					}
				}
				weight = weight - (app.territories.length / ranking_total);
				return weight;
			},
			search(territory_filter) {
				if(search_to) {
					clearTimeout(search_to);
				}
				if(!this.term) {
					return;
				}
				var search_promises = [];
				this.loading = true;
				search_to = setTimeout(() => {
					ga('send', 'event', 'search', this.term);
					for(var t = 0; t < this.territoriesActive.length; t++) {
						if(territory_filter && territory_filter != this.territoriesActive[t]) {
							continue;
						}
						((territory) => {
							var params = {
								country: territory.code,
								limit: search_limit,
								media: 'software',
								term: this.term
							};
							search_promises.push(new Promise((resolve, reject) => {
								jsonp(params, (response) => {
									if(params.term != this.term) {
										console.log('term has changed, ignoring results');
										return;
									}
									for(var i = 0; i < response.results.length; i++) {
										var result = response.results[i];
										var id = result.trackId;
										var app = this.apps.find(a => a.id == id);
										if(!app) {
											app = {
												territories: [],
												id: id,
												name: result.trackName
											}
											for(var tt = 0; tt < this.territoriesActive.length; tt++) {
												this.addTerritoryToApp(app, this.territoriesActive[tt])
											}
											this.apps.push(app);
										}
										var appTerritory = app.territories.find(at => at.territory === territory);
										appTerritory.name = result.trackName;
										appTerritory.available = true;
										appTerritory.ranking = (i + 1);
									}
									resolve();
								}, () => {
									alert('something went wrong during search');
								});
							}));
						})(this.territoriesActive[t]);
					}
					Promise.all(search_promises).then(() => {
						this.sortApps();
						this.loading = false;
						this.supplementAppTerritories();
					});
				}, 500);
			},
			searchAll() {
				this.apps = [];
				search_limit = search_limit_init;
				this.search(null);
			},
			searchTerritory(territory) {
				search_limit = search_limit + search_limit_init;
				this.search(territory);
			},
			setTerm(term) {
				this.term = term;
				this.searchAll();
				window.scrollTo(0, 0);
			},
			sortApps() {
				if(this.sortAppsByTerritoryIndex != null) {
					this.apps.sort((a, b) => {
						return this.appWeightTerritory(a, this.sortAppsByTerritoryIndex) > this.appWeightTerritory(b, this.sortAppsByTerritoryIndex) ? 1 : -1;
					});
					return;
				}

				this.apps.sort((a, b) => {
					return this.appWeightDefault(a) > this.appWeightDefault(b) ? 1 : -1;
				});
			},
			sortAppsBy(click) {
				this.sortAppsByTerritoryIndex = click.target.cellIndex;
				this.sortApps();
			},
			supplementAppTerritories() {
				for(var a = 0; a < this.apps.length; a++) {
					var app = this.apps[a];
					for(var at = 0; at < app.territories.length; at++) {
						var appTerritory = app.territories[at];
						((app, appTerritory) => {
							if(appTerritory.available === null) {
								var params = {
									country: appTerritory.territory.code,
									media: 'software',
									term: app.id
								};
								jsonp(params, (response) => {
									appTerritory.available = false;
									for(var i = 0; i < response.results.length; i++) {
										var result = response.results[i];
										if(result.trackId == app.id) {
											appTerritory.available = true;
											appTerritory.name = result.trackName;
											break;
										}
									}
									if(appTerritory.available) {
										this.sortApps();
									}
								}, () => {
									appTerritory.available = -1;
								});
							}
						})(app, appTerritory);
					}
				}
			}
		},
		mounted: function() {
			this.addTerritoryFromCode('US');
			this.addTerritoryFromCode('CN');
			this.searchAll();
		},
		updated: function() {
			this.setL();
		}
	});
	</script>
