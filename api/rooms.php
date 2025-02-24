<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rooms = get_available_rooms();
    echo json_encode(['status' => 'success', 'data' => $rooms]);
}
