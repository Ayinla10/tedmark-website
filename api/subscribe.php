<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { jsonResponse(['success'=>false,'error'=>'Method not allowed'], 405); }

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$name = trim($input['name'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success'=>false,'error'=>'Invalid email address']);
}

try {
    $existing = fetchOne("SELECT id FROM leads WHERE email=?", [$email]);
    if (!$existing) {
        insert('leads', ['email'=>$email,'name'=>$name,'source'=>'newsletter','ip_address'=>getClientIp()]);
    }
    jsonResponse(['success'=>true,'message'=>'Subscribed successfully!']);
} catch (Exception $e) {
    jsonResponse(['success'=>false,'error'=>'Something went wrong'], 500);
}
