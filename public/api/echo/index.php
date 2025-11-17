<?php
$name = $_POST['name'] ?? '';
$response = $name;
if(rand(0,5) == 5) {
    $response = 'Back in a few! Try again later.';
}

echo 'You shouted your name "' . $name . '" a top of a mountain. Echo wake up and shouted back: ' 
    . strtoupper($response) . PHP_EOL
    . print_r($_POST, true);
