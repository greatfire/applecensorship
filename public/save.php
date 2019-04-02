<?php
require '../inc/main.inc';

if(!isset($_POST)) {
	exit;
}

$request = [];
foreach (VALID_REQUEST_KEYS as $key) {
    if (isset($_POST[$key])) {
        $request[$key] = $_POST[$key];
    }
}

if(!isset($_POST['response'])) {
	exit;
}
$response = json_decode($_POST['response']);
save_request_response($request, $response, 'client');
