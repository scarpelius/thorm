<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$search = $_GET['search'] ?? '';

http_response_code(200); // or 200

$response = [
    ['id' => 1, 'title' => 'Stars and stripes'],
    ['id' => 2, 'title' => 'Storks and sparrows'],
    ['id' => 3, 'title' => 'Light of Elune'],
    ['id' => 4, 'title' => 'Drowned in the middle of desert'],
    ['id' => 5, 'title' => 'Pilbara, crazy kangaroo land'],
    ['id' => 6, 'title' => 'Thiefs from bellow'],
];

$return = [];
foreach($response as $r){
    $pattern = '/' . preg_quote($search, '/') . '/i';

    if (preg_match($pattern, $r['title']) === 1) {
        $return[] = $r;
    }
} 
if(count($return) == 0) {
    $return[] = ['id' => 1, 'title' => 'No match.'];
}

echo json_encode($return);
