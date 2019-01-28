<?php
require '../inc/main.inc';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Apple Censorship</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
	font-family: sans-serif;
}
h1 a, h2 a {
	color: black;
	text-decoration: none;
}
h1 {
	margin-bottom: 0;
}
h2 {
	margin-top: 0;
}
#search input {
	box-sizing: border-box;
	font-size: 150%;
	max-width: 100%;
	padding: 1%;
}
table {
	max-width: 100%;
}
tbody {
	word-break: break-all;
}
th, td {
	padding: 1%;
}
th {
	cursor: pointer;
	text-align: left;
}
th select {
	max-width: 50px;
}
td.a {
	border: solid 2px green;
}
td.na {
	border: solid 2px red;
	font-weight: bold;
}
td img {
	max-width: 50px;
}

#feedback {
	margin-top: 100px;
}
#feedback span {
	cursor: pointer;
	text-decoration: underline;
}
#feedback textarea, #feedback input {
	border-radius: 5px;
	box-sizing: border-box;
	display: block;
	margin-bottom: 5px;
	max-width: 100%;
	padding: 5px;
	width: 400px;
}
#feedback button {
	display: block;
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mithril/1.1.6/mithril.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js"></script>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-26222920-44', 'auto');
ga('send', 'pageview');
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-26222920-44');
</script>
</head>
<body>
	<h1><a href="/">Apple Censorship</a></h1>
	<h2>by <a href="https://greatfire.org/" target="_blank">GreatFire<a></h2>
	<div id="app">
		<div id="search"><input type="text" v-model="term" @input="searchAll" ref="term" autofocus placeholder="Search the App Store"></div>
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
							<a :href="'https://itunes.apple.com/' + appTerritory.territory.code.toLowerCase() + '/app/' + appTerritory.name + '/id' + app.id" target="_blank">{{ appTerritory.name }}</a><span v-if="appTerritory.ranking"> ({{ appTerritory.ranking }})</span>
						</span>
						<a v-if="appTerritory.available === false" :href="'https://itunes.apple.com/' + appTerritory.territory.code.toLowerCase() + '/app/' + app.name + '/id' + app.id" target="_blank">N/A</a>
						<img v-if="appTerritory.available === null" src="ajax-loader.gif">
						<span v-if="appTerritory.available === -1">error</span>
					</td>
				</tr>
				<tr>
					<td v-for="territory in territoriesActive">
						<button v-if="loading === false" @click="searchTerritory(territory)">+</button>
						<img v-if="loading === true" src="ajax-loader.gif">
					</td>
				</tr>
			</tbody>
		</table>
		<div v-if="!loading && apps.length == 0 && term.length > 0">No matches</div>
		<div id="feedback">
			<div v-if="!show_feedback">
				Did you find a bug or do you have an idea for how to improve this website?
				<br><span @click.stop="show_feedback = true">Please tell us what you think</span>.
			</div>
			<div v-if="show_feedback">
				<p>Please send a message to support@greatfire.org or fill in this form. We appreciate your feedback.</p>
				<textarea placeholder="Message" v-model="feedback_message">
				</textarea>
				<input type="text" placeholder="Email address (optional)" v-model="feedback_email">
				<button @click.stop="send_feedback()">Send</button>
			</div>
		</div>
	</div>
	<script>
	var api_urls = ['<?php print ITUNES_API_URL ?>', 'api.php'];
	var search_limit_init = 10;
	var search_limit = search_limit_init;
	var search_to;

	function jsonp(params, then, fail, urls_remaining) {
		if(urls_remaining == undefined) {
			urls_remaining = api_urls.slice();
		} else if(urls_remaining.length == 0) {
			console.error('No remaining urls', params);
			fail();
			return;
		}
		var url = urls_remaining.shift();
		return m.jsonp(url, {
			data: params
		}).then(then).catch(function(err) {
			jsonp(params, then, fail, urls_remaining);
		});
	}

	new Vue({
		el: '#app',
		data: {
			apps: [],
			feedback_email: '',
			feedback_message: '',
			loading: false,
			selectedTerritoryIndex: null,
			show_feedback: false,
			sortAppsByTerritoryIndex: 0,
			term: '',
			territoriesActive: [],
			territoriesInactive: <?php
			$territories = [];
			foreach(file('../territories.tsv', FILE_IGNORE_NEW_LINES) as $line) {
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
					gtag('event', 'search', {'event_category': this.term});
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
			send_feedback() {
				console.log('send');
				if(!this.feedback_message) {
					return;
				}
				ga('send', 'event', 'feedback', this.feedback_message, this.feedback_email, {
					hitCallback: () => {
						alert('Thank you for your feedback!');
					},
					hitCallbackFail: () => {
						alert('Something went wrong, please try again later.');
					}
				});
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
			this.$refs.term.focus();
			this.addTerritoryFromCode('US');
			this.addTerritoryFromCode('CN');
		}
	});
	</script>
</body>
</html>
