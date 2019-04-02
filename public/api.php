<?php
require '../inc/main.inc';

const CACHE_EXPIRY = 86400;

$url = ITUNES_API_URL;
if (! isset($_GET['callback'])) {
    fatal('Missing callback string');
}
define('CALLBACK', $_GET['callback']);
unset($_GET['callback']);

$request = [];
foreach (VALID_REQUEST_KEYS as $key) {
    if (isset($_GET[$key])) {
        $request[$key] = $_GET[$key];
    }
}

$url .= '?' . http_build_query($request);

function getJson()
{
    global $mongodb_manager, $request, $url;

    $filter = [
        'request' => $request,
	'source' => 'server',
	'ts' => [
            '$gt' => time() - CACHE_EXPIRY
        ]
    ];
    $options = [
        'limit' => 1,
        'sort' => [
            'ts' => - 1
        ]
    ];
    $mongodb_query = new MongoDB\Driver\Query($filter, $options);
    $rows = $mongodb_manager->executeQuery(MONGODB_COLLECTION, $mongodb_query);
    foreach ($rows as $row) {
        if (isset($row->response)) {
            $json = json_encode($row->response);
            return $json;
        }
    }

    for ($i = 0; $i < 99; $i ++) {
        $timeout = 10 + pow($i, 2);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, PROXY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $json = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($json);
        if (! $response) {
            continue;
        }

        if (! isset($response->resultCount)) {
            continue;
        }

	save_request_response($request, $response, 'server');

        return $json;
    }
}

$json = getJson($url);
if ($json) {
    print CALLBACK;
    print '(';
    print trim($json);
    print ');';
} else {
    fatal('empty response');
}
