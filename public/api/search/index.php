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
    ['id' => 5, 'title' => 'Pilbara, țara cangurului nebun'],
    ['id' => 6, 'title' => 'Hoții din adâncuri'],
];

echo json_encode($response);
