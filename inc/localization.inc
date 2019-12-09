<?php

require dirname(__DIR__) . '/localization/languages.inc';

function getPreferredLanguageCode() {
	static $preferred;
	if(isset($preferred)) {
		return $preferred;
	}

	global $languages;
	$fallback = 'en';

	if(isset($_GET['l'])) {
		if(isset($languages[$_GET['l']])) {
			$preferred = $_GET['l'];
			return $preferred;
		}
	}

	$l = $fallback;
	foreach(array_keys($languages) as $language) {
		if(stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], $language) !== false) {
			$l = $language;
			break;
		}
	}

	$new_uri = $_SERVER['REQUEST_URI'];
	$new_uri .= (strpos($new_uri, '?') === false) ? '?' : '&';
	$new_uri .= 'l=' . $fallback;
	header('Location: ' . $new_uri);
	exit;
}

function p($key, $preferred = null) {
	print t($key, $preferred);
}

function pf(...$args) {
	print tf(...$args);
}

function t($key, $preferred = null) {
	global $languages;

	$fallback = 'en';
	if(!$preferred) {
		$preferred = getPreferredLanguageCode();
	}

	foreach([$preferred, $fallback] as $l => $language) {
		if(isset($languages[$language][$key])) {
			$value = $languages[$language][$key];
			$value = trim($value);
			if($value) {
				if($l > 0) {
					$value = '[[' . $value . ']]';
				}
				return $value;
			}
		}
	}

	$missing_path = dirname(__DIR__) . '/localization/missing.json';
	$data = json_decode(file_get_contents($missing_path));
	if(!in_array($key, $data)) {
		$data[] = $key;
	}
	file_put_contents($missing_path, json_encode($data));

	return $key;
}

function tf($string) {
	$string = t($string);
	$args = func_get_args();
	for($i = 1; $i < count($args); $i++) {
		$string = str_replace('$' . $i, $args[$i], $string);
	}
	return $string;
}
