<?php
include __DIR__ . '/Api.php';
///js/bootstrap.bundle.min.js.map


if ($_SERVER['REQUEST_URI'] == '/favicon.ico') {
    echo '';
    exit();
}

if ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '') {
    include __DIR__ . '/index.html';
    exit();
}

$api = new Api();

if ($_SERVER['REQUEST_URI'] == '/getData') {
    header('Content-Type: application/json');
    echo json_encode($api->getData());
    exit();
}

if ($_SERVER['REQUEST_URI'] == '/changeData') {
    header('Content-Type: application/json');
    $api->changeData($_POST);
    echo json_encode(['status' => 'ok']);
    exit();
}

if (empty($_GET['data'])) {
    exit();
}

file_put_contents('get.json', json_encode($_GET['data']));

$api->saveMonitor(json_decode($_GET['data'], true));
$response = $api->getAndChangeParams(json_decode($_GET['data'], true));
//file_put_contents('test.json', json_encode($_POST));

header("Content-Type: application/json");

echo json_encode($response);
exit();