<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();
$allowedOrigin = "http://localhost:8080";

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header("Access-Control-Allow-Origin: $allowedOrigin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $DB = "chikachat";
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB connection failed','error'=>$e->getMessage()]);
    exit;
}

// MongoDB connection
$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->chikachat;

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $data = json_decode(file_get_contents("php://input"), true);
    $hashed = password_hash($data['password'], PASSWORD_BCRYPT);

    if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        echo json_encode([
            'status' => 'error'
            // 'message' => 'Some fields are missing.'
        ]);
        exit();
    }

    $db->users->insertOne([
        'username' => $data['username'],
        'email' => $data['email'],
        'password_hash' => $hashed,
        'status' => 'offline',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['status' => 'success']);
    exit();
}
elseif ($action === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user = $db->users->findOne(['username' => $data['username']]);

    if ($user && password_verify($data['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = (string)$user['_id'];
        $_SESSION['username'] = $user['username'];

        // echo "Fetched.";
        // header('/panel.php');
        // exit();

        echo json_encode([
            'status' => 'success',
            'user' => [
                '_id' => (string)$user['_id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        exit();
    }
} elseif ($action === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: /index.html');
    exit();
} elseif ($action === 'create_group') {
    $data = json_decode(file_get_contents("php://input"), true);

    $db->groups->insertOne([
        'name' => $data['name'],
        'members' => $data['members'], // array of userIds
        'created_by' => $data['created_by'],
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Group created']);
    exit();
} elseif ($action === 'get_users') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Not logged in"
        ]);
        exit;
    }

    $currentUserId = $_SESSION['user_id'];

    // Find all users except the current one
    $filter = ['_id' => ['$ne' => new MongoDB\BSON\ObjectId($currentUserId)]];
    $opts   = ['projection' => ['username' => 1, 'email' => 1]];
    $q      = new MongoDB\Driver\Query($filter, $opts);
    $cursor = $manager->executeQuery("$DB.users", $q);

    $users = [];
    foreach ($cursor as $u) {
        $users[] = [
            'id'       => (string)$u->_id,
            'username' => $u->username ?? '',
            'email'    => $u->email ?? ''
        ];
    }

    echo json_encode([
        "status" => "success",
        "users"  => $users
    ]);
    exit;
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit();
}
