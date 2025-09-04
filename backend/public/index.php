<?php
require_once __DIR__ . '/../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

// MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->chikachat;

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $data = json_decode(file_get_contents("php://input"), true);
    $hashed = password_hash($data['password'], PASSWORD_BCRYPT);

    $db->users->insertOne([
        'username' => $data['username'],
        'email' => $data['email'],
        'password_hash' => $hashed,
        'status' => 'offline',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['status' => 'success']);
}
elseif ($action === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user = $db->users->findOne(['username' => $data['username']]);

    if ($user && password_verify($data['password'], $user['password_hash'])) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                '_id' => (string)$user['_id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
} elseif ($action === 'create_group') {
    $data = json_decode(file_get_contents("php://input"), true);

    $db->groups->insertOne([
        'name' => $data['name'],
        'members' => $data['members'], // array of userIds
        'created_by' => $data['created_by'],
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Group created']);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
