<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$search = $_GET['search'] ?? '';

http_response_code(200); // or 200

$response = [
    ['id' => 1, 'title' => 'Dune - Frank Herbert (1965)'],
    ['id' => 2, 'title' => 'Neuromancer - William Gibson (1984)'],
    ['id' => 3, 'title' => 'The Left Hand of Darkness - Ursula K. Le Guin (1969)'],
    ['id' => 4, 'title' => 'Snow Crash - Neal Stephenson (1992)'],
    ['id' => 5, 'title' => 'The Three-Body Problem - Liu Cixin (2006)'],
    ['id' => 6, 'title' => 'The Expanse: Leviathan Wakes - James S. A. Corey (2011)'],
    ['id' => 7, 'title' => 'Hyperion - Dan Simmons (1989)'],
    ['id' => 8, 'title' => 'Red Mars - Kim Stanley Robinson (1992)'],
    ['id' => 9, 'title' => 'Annihilation - Jeff VanderMeer (2014)'],
    ['id' => 10, 'title' => 'The Forever War - Joe Haldeman (1974)'],
    ['id' => 11, 'title' => 'The Windup Girl - Paolo Bacigalupi (2009)'],
    ['id' => 12, 'title' => 'Solaris - Stanisław Lem (1961)'],
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
