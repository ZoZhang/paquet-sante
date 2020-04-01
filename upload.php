<?php
$response = [
    'error' => true,
    'message' => '服务器内部错误，请稍后再试!'
];

if (!isset($_FILES['table']) || !count($_FILES['table'])) {
    print_r(json_encode($response));
    exit;
}

