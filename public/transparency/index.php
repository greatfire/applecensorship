<?php

require '../template.php';

?>
<h3>
	<?php 
	$ids = [1064216828, 284862083, 304878510];
	$links = [];
	foreach($ids as $id) {
		$app = get_app($id);
		$links[] = '<a href="/app/' . $app->_id . '">' . $app->name . '</a>';
	}
	pf('$1 apps are unavailable in the $2, including $3, $4 and $5. Apple claims - without providing any evidence - that the majority of these are related to gambling or pornography.', $mongodb_manager->executeQuery('ac.territories', new MongoDB\Driver\Query(['_id' => 'CN']))->toArray()[0]->apps_unavailable, '<a href="/na/CN">' . t('China (mainland) App Store') . '</a>', $links[0], $links[1], $links[2]);
	?>
</h3>
<br>
<div class="quotes">
	<div>
		<q><?php print t('Government requests related to app removals: China mainland - The vast majority relate to illegal gambling or pornography.') ?></q>
		<a href="https://www.apple.com/legal/transparency/cn.html" target="_blank"><?php print t('Apple Transparency Report') ?></a>
	</div>
	<div>
		<q><?php print t('Guess what? We didn’t know that Apple was helping the CCP to block Bitter Winter in China. We just learned it from The Intercept, actually reading one of its well-documented and well-researched articles published more than 20 days ago. We are thankful to that online magazine because they read us and checked if people in China could do the same.') ?></q>
		<a href="https://bitterwinter.org/apple-cooperates-with-the-ccp-to-censor-bitter-winter/" target="_blank"><?php print t('Bitter Winter') ?></a>
	</div>
	<div>
		<q><?php print t('Apple disclosed little about its rules and how they are enforced, and revealed no data about content removed—including apps removed from its App Store—as a result of government requests.') ?></q>
		<a href="https://rankingdigitalrights.org/index2019/companies/apple/index/" target="_blank"><?php print t('Ranking Digital Rights Corporate Accountability Index 2019') ?></a>
	</div>
	<?php /*
	<div>
		<q><?php print t('') ?></q>
		<a href="" target="_blank"><?php print t('') ?></a>
		<a href="" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
	<?php */ ?>
</div>
