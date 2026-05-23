<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

$categories = [
    ["code" => "level1", "name" => "Level 1", "description" => "Motorcycle"],
    ["code" => "level2", "name" => "Level 2", "description" => "Private Car"],
    ["code" => "level3", "name" => "Level 3", "description" => "Heavy Truck"],
    ["code" => "level4", "name" => "Level 4", "description" => "People Transportation"],
    ["code" => "level5", "name" => "Level 5", "description" => "Bus Driver"]
];

echo json_encode([
    "success" => true,
    "data" => $categories
]);
?>
