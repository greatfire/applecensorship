<?php
require_once dirname(__DIR__) . '/inc/main.inc';
$term = '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php p('Apple Censorship') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
@font-face {
	font-family: "PingFang SC Regular";
	src: url('/css/PingFangSC-Regular.woff') format('woff');
}

body {
	font-family: "PingFang SC Regular";
	font-weight: normal;
	margin: 1vw 0;
}
@media only screen and (max-width: 500px) {
	body {
		font-size: 4vw;
	}
}

body>* {
	padding: 0 1vw;
}

body>br {
	clear: both;
	display: block;
	margin: .5vw;
}

h1, h2, h3 {
	font-size: 25px;
	font-weight: normal;
	margin: 0;
}
@media only screen and (min-width: 501px) {
	h1 {
		float: left;
	}
	h2 {
		float: right;
	}
	#search {
		float: left;
	}
}
h1 img, h2 img {
	margin: 0 0 -3px 12px;
}
h1 a {
	color: #FF473C;
	text-decoration-color: #FF473C;
}
h2 a {
	color: black;
	text-decoration-color: black;
}

ul {
	background: #FE2D58;
	font-size: 25px;
	list-style: none;
	margin: 5px 0;
	padding-top: 5px;
	padding-bottom: 5px;
}
@media only screen and (min-width: 501px) {
	ul {
		column-count: 2;
	}
}
ul a {
	color: white;
}
li {
	margin-top: 5px;
}

#search input, #search button {
	box-sizing: border-box;
	font-size: 150%;
	max-width: 100%;
	padding: .5vw;
}

#search .matches {
	padding: 0 1vw;
}

table {
	max-width: 100%;
}
tbody {
}
th, td {
	border-collapse: separate;
	border-spacing: 0px;
	padding: 1vw;
	vertical-align: top;
}
th {
	cursor: pointer;
	text-align: left;
}
th select {
	max-width: 100px;
}
td {
	word-break: break-all;
}
td.a {
	border: solid 2px green;
}
td.na {
	border: solid 2px red;
	font-weight: bold;
}
td img {
	margin-right: 1vw;
	max-width: 50px;
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

.quotes {
	column-gap: 1vw;
}
@media only screen and (min-width: 450px) {
	.quotes {
		column-count: 2;
	}
}
@media only screen and (min-width: 850px) {
	.quotes {
		column-count: 3;
	}
}
@media only screen and (min-width: 1500px) {
	.quotes {
		column-count: 5;
	}
}

.quotes>div {
	background-color: #4D4D4D;
	background-image: url(/img/quote.png);
	background-position: top right;
	background-repeat: no-repeat;
	border: thin solid #979797;
	box-sizing: border-box;
	color: white;
	display: inline-block;
	font-size: 30px;
	margin-bottom: 10px;
	padding: 30px 35px 15px 15px;
}

.quotes a {
	color: white;
	display: block;
	font-size: 20px;
}

#footer {
	border-top: 10px dashed #aaa;
	margin-top: 10vh;
	padding: 1vh;
}
#footer div {
	padding-top: 1vh;
}
</style>
<script src="https://unpkg.com/mithril@1.1.6/mithril.min.js"></script>
<script src="https://unpkg.com/vue@2.6.10/dist/vue.min.js"></script>
<script src="https://unpkg.com/vue-lazyload@1.2.6/vue-lazyload.js"></script>
<script src="/md5.min.js"></script>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-26222920-44', 'auto');
ga('send', 'pageview');
</script>
<script>
Vue.component('download-data', {
	data: function() {
		return {
			csv: '',
			json: ''
		}
	},
	methods: {
		arr2csv: function() {
			let rows = [];
			rows.push(Object.keys(this.data[0]));
			for(let i = 0; i < this.data.length; i++) {
				let row = [];
				for(j = 0; j < rows[0].length; j++) {
					let v = this.data[i][rows[0][j]];
					row.push(v);
				}
				rows.push(row);
			}

			let lines = [];
			for(let i = 0; i < rows.length; i++) {
				for(let j = 0; j < rows[i].length; j++) {
					if(typeof(rows[i][j]) == 'string' && rows[i][j].indexOf(' ') !== -1) {
						rows[i][j] = '"' + rows[i][j] + '"';
					}
				}
				lines.push(rows[i].join(','));
			}
			return lines.join("\n");
		},
		setCsv: function() {
			this.csv = 'data:text/txt;charset=utf-8,' + encodeURIComponent(this.arr2csv());
		},
		setJson: function() {
			this.json = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(this.data))
		}
	},
	props: ['data', 'filename'],
	template: '<div><?php p('Download data as ') ?>' +
		'<a v-bind:href="csv" v-bind:download="\'applecensorship.com-\' + filename + \'.csv\'" @click="setCsv()">CSV</a>, ' +
		'<a v-bind:href="json" v-bind:download="\'applecensorship.com-\' + filename + \'.json\'" @click="setJson()">JSON</a>' +
		'</div>'
});

Vue.mixin({
	methods: {
		setL: function() {
			this.setLOnElAttr('a', 'href');
			this.setLOnElAttr('form', 'action');
		},
		setLOnElAttr: function(tag, attr) {
			let l = '<?php print $_GET['l'] ?>';
			if(!l) {
				return;
			}
			let al = '?l=' + l;
			let alr = new RegExp(al.replace('?', '\\?'));
			let els = document.getElementsByTagName(tag);
			for(let i = 0; i < els.length; i++) {
				let el = els[i];
				let v = el.getAttribute(attr);
				if(!v || v.search(/^(data|https?):/) !== -1) {
					continue;
				}
				if(!alr.test(v)) {
					el.setAttribute(attr, v + al);
				}
			}
		}
	}
});
</script>
</head>
<body>
	<h1>
		<a href="/"><?php p('Apple Censorship') ?><img src="/img/home.png"></a>
	</h1>
	<h2>
		<?php p('By') ?> <a href="https://greatfire.org/" target="_blank"><?php p('GreatFire') ?><img src="/img/external.png"></a>
	</h2>
	<br>
	<div id="search">
		<input v-model="term" type="text" @keyup="search()">
		<button @click="test()">Test</button>
		<div v-if="matches" class="matches">
			<div v-for="(title, id) in matches">
				<a v-bind:href="'/app/' + id">{{ title }}</a>
			</div>
		</div>
	</div>
	<br>
	<ul>
		<li><a href="/na"><?php p('App Store Overview') ?></a></li>
		<li><a href="/changes"><?php p('Detected Changes') ?></a></li>
		<li><a href="/free-speech"><?php p('Apple and Free Speech') ?></a></li>
		<li><a href="/privacy"><?php p('Apple and Privacy') ?></a></li>
		<li><a href="/transparency"><?php p('Apple and Transparency') ?></a></li>
		<li><a href="/law"><?php p('Apple and Rule of Law') ?></a></li>
	</ul>
	<br>
<?php
register_shutdown_function(function() {
	global $languages, $term;
?>
<div id="footer">
	<div id="l">
		<select onchange="var l = window.location, h = l.href.replace(/[?&]?l=[a-z]+/, ''); h = h + (h.match(/\?/) ? '&' : '?') + 'l=' + this.value; window.location = h">
		<?php foreach(array_keys($languages) as $language) { ?>
			<option<?php if($language == $_GET['l']) { print ' selected'; } ?> value="<?php print $language ?>"><?php print t('language_name', $language) ?></option>
		<?php } ?>
		</select>
	</div>
	<div id="feedback">
		<div v-if="!show_feedback">
			<?php p('Did you find a bug or do you have an idea for how to improve this website') ?>
			<?php pf('Send an email to $1.', 'greatfire@greatfire.org') ?>
		</div>
	</div>
	<div>
		<a href="https://github.com/greatfire/applecensorship" target="_blank">
			<img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" height="50px">
		</a>
	</div>
</div>
<script>
new Vue({
	el: '#search',
	created: function() {
		this.setL();
	},
	data: {
		matches: null,
		term: '<?php print $term ?>'
	},
	methods: {
		search: function() {
			if(!this.term) {
				return;
			}
			fetch('/search.php?term=' + encodeURIComponent(this.term)).then(response => response.json()).then(response => {
				this.matches = response;
			});
		},
		test: function() {
			if(!this.term) {
				return;
			}
			let l = '<?php print $_GET['l'] ?>';
			window.location = '/test/' + encodeURIComponent(this.term) + '?l=' + l;
		}
	}
});
</script>
</body>
</html>
<?php
});
