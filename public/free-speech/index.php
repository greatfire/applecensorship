<?php

require '../template.php';

?>
<h3>
	<?php 
	$ids = [281941097, 429047995, 1064216828];
	$links = [];
	foreach($ids as $id) {
		$app = get_app($id);
		$links[] = '<a href="/app/' . $app->_id . '">' . $app->name . '</a>';
	}
	pf('Apple claims to defend human rights, yet thousands of apps are selectively made unavailable in $1 around the world. Examples include $2, $3, and $4, which are all censored by Apple in $5.', '<a href="/na">' . t('App Stores') . '</a>', $links[0], $links[1], $links[2], '<a href="/na/CN">' . t('China') . '</a>')
	?>
</h3>
<br>
<div>
	<div class="quote">
		<q><?php print t('At Apple, we are not afraid to say that our values drive our curation decisions.') ?></q>
		<a href="https://www.nbcnews.com/business/business-news/apple-ceo-tim-cook-says-tech-needs-take-moral-stand-n943386" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
	<div class="quote">
		<q><?php print t("Apple severs a bridge to our most avid and critical readers for China news -- our Chinese readers. Doesn't Apple owe them an explanation?") ?></q>
		<a href="https://twitter.com/ChuBailiang/status/816829590647676928" target="_blank"><?php print t('Chris Buckley, New York Times reporter, after Apple removed  the New York Times app from the China App Store') ?></a>
	</div>
	<div class="quote">
		<q><?php print t('I think every generation has the responsibility to enlarge the meaning of human rights.') ?></q>
		<a href="https://www.washingtonpost.com/sf/business/2016/08/13/tim-cook-the-interview-running-apple-is-sort-of-a-lonely-job/?utm_term=.8805b9ceb2b6" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
	<div class="quote">
		<q><?php print t('American tech companies have become leading champions of free expression. But that commitment should not end at our borders. Global leaders in innovation, like Apple, have both an opportunity and a moral obligation to promote free expression and other basic human rights in countries that routinely deny these rights.') ?></q>
		<a href="https://www.cnbc.com/2017/12/05/apple-in-china-senator-patrick-leahy-reacts-to-tim-cook-speech.html" target="_blank"><?php print t('Senator Patrick Leahy') ?></a>
	</div>
	<div class="quote">
		<q><?php print t('First we defend, we work to defend these freedoms by enabling people around the world to speak up. And second, we do it by speaking up ourselves. Because companies can, and should have values. At Apple we are not just enabling others to speak up, we are doing so ourselves.') ?></q>
		<a href="https://appleinsider.com/articles/17/04/18/tim-cook-accepts-newseum-2017-free-expression-award-says-companies-should-have-values" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
</div>
<br>
