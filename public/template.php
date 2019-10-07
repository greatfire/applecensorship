<?php
require_once dirname(__DIR__) . '/inc/main.inc';
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
	font-family: Helvetica;
	margin: 1vw 0;
}
body>* {
	padding: 0 1vw;
}
body>br {
	clear: both;
	display: block;
	margin: .5vw;
}

@media only screen and (max-width: 500px) {
	body {
		font-size: 4vw;
	}
}
@media only screen and (min-width: 501px) {
	h1 {
		float: left;
	}
	h2 {
		float: right;
	}

	#l {
		display: block;
		float: right;
	}

	.quote {
		max-width: 300px;
	}
}

h1, h2, h3 {
	font-family: "PingFang SC Regular";
	font-size: 25px;
	font-weight: normal;
	margin: 0;
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
	font-family: "PingFang SC Regular";
	font-size: 25px;
	font-weight: normal;
	list-style: none;
	margin: 0;
	padding-top: 5px;
	padding-bottom: 5px;
}
ul a {
	color: white;
}
li {
	margin-top: 5px;
}

#search input {
	box-sizing: border-box;
	font-size: 150%;
	max-width: 100%;
	padding: 1vw;
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

#searches, #feedback {
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

.quote {
	background-color: #4D4D4D;
	background-image: url(/img/quote.png);
	background-position: top right;
	background-repeat: no-repeat;
	border: thin solid #979797;
	box-sizing: border-box;
	color: white;
	float: left;
	font-family: "PingFang SC Regular";
	font-size: 30px;
	font-weight: normal;
	margin: 0 1vw 10px 0;
	padding: 30px 35px 15px 15px;
}

.quote a {
	color: white;
	display: block;
	font-size: 20px;
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
Vue.mixin({
	methods: {
		setL: function() {
			let l = '<?php print $_GET['l'] ?>';
			if(!l) {
				return;
			}
			let hl = '?l=' + l;
			let hlr = new RegExp(hl.replace('?', '\\?'));
			let as = document.getElementsByTagName('a');
			for(let i = 0; i < as.length; i++) {
				let a = as[i];
				let h = a.getAttribute('href');
				if(h.search(/^https?:/) !== -1) {
					continue;
				}
				if(!hlr.test(h)) {
					a.setAttribute('href', h + hl);
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
	<div id="l">
		<select onchange="var l = window.location, h = l.href.replace(/[?&]?l=[a-z]+/, ''); h = h + (h.match(/\?/) ? '&' : '?') + 'l=' + this.value; window.location = h">
		<?php foreach(array_keys($languages) as $language) { ?>
			<option<?php if($language == $_GET['l']) { print ' selected'; } ?> value="<?php print $language ?>"><?php print t('language_name', $language) ?></option>
		<?php } ?>
		</select>
	</div>
	<br>
	<ul>
		<li><a href="/"><?php p('Search') ?></a></li>
		<li><a href="/free-speech"><?php p('Apple and Free Speech') ?></a></li>
		<li><a href="/na"><?php p('App Store Overview') ?></a></li>
		<li><a href="/changes"><?php p('Detected Changes') ?></a></li>
	</ul>
	<br>
<?php
register_shutdown_function(function() {
	print <<<EOF
<script>
new Vue({
	created: function() {
		this.setL();
	}
});
</script>
</body>
</html>
EOF;
});
