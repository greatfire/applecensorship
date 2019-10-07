<?php
header('Content-Type: application/rss+xml; charset=utf-8');
require '../../inc/main.inc';
require 'rows.inc';
$rows = array_filter($rows, function($row) {
	if($row->change == -1) {
		return true;
	}
	return false;
});
?>
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>AppleCensorship</title>
    <link>https://applecensorship.com/</link>
    <language>en-us</language>
<?php foreach($rows as $row) { ?>
    <item>
      <title><?php print $row->app->name ?></title>
      <description><?php print $row->app->name . ' was removed by Apple in the ' . $row->territoryName . ' App Store' ?></description>
      <link>https://applecensorship.com/app/<?php print $row->app->_id ?></link>
      <pubDate><?php print date('D, j M Y H:i:s O', $row->ts) ?></pubDate>
    </item>
<?php } ?>
  </channel>
</rss>
