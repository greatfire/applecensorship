<?php

require '../template.php';

?>
<h3>
	<?php pf('Apple claims to protect your privacy, while at the same time censoring every single one of the most popular VPN apps in $1, forcing users to rely on lesser-known, less trustworthy apps.', '<a href="/na/CN">' . t('China') . '</a>') ?>
</h3>
<br>
<div class="quotes">
	<div>
		<q><?php print t('Apple products are designed to protect your privacy. At Apple, we believe privacy is a fundamental human right.') ?></q>
		<a href="https://www.apple.com/privacy/" target="_blank">Apple.com</a>
	</div>
	<div>
		<q><?php print t("We understand the dangers are real from rogue nation states. We're not willing to leave our users to fend for themselves and we've shown we will defend them. We will defend our principles when challenged.") ?></q>
		<a href="https://www.youtube.com/watch?v=kVhOLkIs20A" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
	<div>
		<q><?php print t('While Apple may claim it treats its customers equally, some are less equal than others. Profits should never threaten privacy. It’s time for Apple to Think Different when it comes to the privacy of its millions of Chinese customers.') ?></q>
		<a href="https://www.amnesty.org/en/latest/news/2018/03/apple-privacy-betrayal-for-chinese-icloud-users/" target="_blank"><?php print t('Nicholas Bequelin, East Asia Director at Amnesty International') ?></a>
	</div>
	<?php /*
	<div>
		<q><?php print t('') ?></q>
		<a href="" target="_blank"><?php print t('') ?></a>
	</div>
	<?php */ ?>
</div>
