<?php
require_once dirname(__DIR__) . '/inc/main.inc';
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
	margin: 1vw;
}
@media only screen and (max-width: 500px) {
	body {
		font-size: 4vw;
	}
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
</head>
<body>
	<h1><a href="/">Apple Censorship</a></h1>
	<h2>by <a href="https://greatfire.org/" target="_blank">GreatFire</a></h2>
	<ul>
		<li><a href="/">Search</a></li>
		<li><a href="/na">App Store Overview</a></li>
	</ul>
<?php
register_shutdown_function(function() {
	print <<<EOF
</body>
</html>
EOF;
});
