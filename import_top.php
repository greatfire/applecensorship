<?php

require 'inc/main.inc';

$urls = [
	'https://rss.itunes.apple.com/api/v1/us/ios-apps/top-free/all/100/explicit.json',
	'https://rss.itunes.apple.com/api/v1/us/ios-apps/top-paid/all/100/explicit.json'
];
foreach($urls as $url) {
	$json = file_get_contents($url);
	$data = json_decode($json);
	foreach($data->feed->results as $result) {
		$app = get_app($result->id);
		if(!$app) {
			$ids[] = $result->id;
		} else {
		}
	}
}

print_r($ids);
