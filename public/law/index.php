<?php

require '../template.php';

?>
<h3>
	<?php pf("Apple claims that censorship is a legal requirement, while they ignore human rights law and refuse to provide any information about the legal basis.") ?>
</h3>
<br>
<div class="quotes">
	<div>
		<q><?php print t('We would obviously rather not remove the apps, but like we do in other countries we follow the law wherever we do business.') ?></q>
		<a href="https://qz.com/1044199/tim-cook-defends-apples-removal-of-vpn-apps-from-its-chinese-app-store/" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
	<div>
		<q><?php print t('The United States government has demanded that Apple take an unprecedented step which threatens the security of our customers. We oppose this order, which has implications far beyond the legal case at hand. This moment calls for public discussion, and we want our customers and people around the country to understand what is at stake.') ?></q>
		<a href="https://www.apple.com/customer-letter/" target="_blank"><?php print t('Tim Cook, CEO of Apple Inc.') ?></a>
	</div>
	<div>
		<q><?php print t('Everyone has the right to freedom of opinion and expression; this right includes freedom to hold opinions without interference and to seek, receive and impart information and ideas through any media and regardless of frontiers.') ?></q>
		<a href="https://www.un.org/<?php print getPreferredLanguageCode() ?>/universal-declaration-human-rights/index.html" target="_blank"><?php print t('Universal Declaration of Human Rights') ?></a>
	</div>
	<div>
		<q><?php print t("Citizens of the People's Republic of China enjoy freedom of speech, of the press, of assembly, of association, of procession and of demonstration.") ?></q>
		<a href="<?php print t('http://www.npc.gov.cn/zgrdw/englishnpc/Constitution/2007-11/15/content_1372964.htm') ?>" target="_blank"><?php print t("Constitution Of The People's Republic Of China") ?></a>
	</div>
	<div>
		<q><?php print t('All they stated was that they had received a request from Chinese authorities to remove the app from the Chinese store because it goes against local laws, and that they consider this a matter between the Chinese authorities and RNW. Apple makes it impossible for apps concerned with issues such as free speech or human rights to find a home in the Chinese App store, especially because a phone call from authorities is considered sufficient proof to Apple that certain apps offend local law.') ?></q>
		<a href="https://web.archive.org/web/20160404091329/https://www.rnw.org/archive/apple-blocks-app-developed-chinese-activists-and-rnw" target="_blank"><?php print t('Radio Netherlands Worldwide') ?></a>
	</div>
	<div>
		<q><?php print t('Human rights law gives companies a language to articulate their positions worldwide in ways that respect democratic norms and counter authoritarian demands. It is much less convincing to say to authoritarians, “We cannot take down that content because that would be inconsistent with our rules,” than it is to say, “Taking down that content would be inconsistent with the international human rights our users enjoy and to which your government is obligated to uphold”.') ?></q>
		<a href="https://onezero.medium.com/a-new-constitution-for-content-moderation-6249af611bdf" target="_blank"><?php print t('David Kaye, from his book “Speech Police”') ?></a>
	</div>
	<?php /*
	<div>
		<q><?php print t('') ?></q>
		<a href="" target="_blank"><?php print t('') ?></a>
	</div>
	<?php */ ?>
</div>
