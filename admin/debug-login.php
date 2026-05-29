<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "<pre style='background:#1e293b;color:#e2e8f0;padding:20px;font-size:14px;'>";

// 1. Test DB connection
$db = db();
echo "DB Connection: " . ($db ? "✅ OK\n" : "❌ FAILED\n");

// 2. Check users table
try {
    $users = fetchAll("SELECT id, name, email, role, LEFT(password,20) as pass_preview FROM users");
    echo "Users in DB: " . count($users) . "\n";
    foreach ($users as $u) {
        echo "  - [{$u['id']}] {$u['name']} | {$u['email']} | {$u['role']} | hash starts: {$u['pass_preview']}...\n";
    }
} catch (Exception $e) {
    echo "Error reading users: " . $e->getMessage() . "\n";
}

// 3. Test password verify
$testUser = fetchOne("SELECT * FROM users LIMIT 1");
if ($testUser) {
    $testPass = $_GET['p'] ?? '';
    if ($testPass) {
        $result = password_verify($testPass, $testUser['password']);
        echo "\nTesting password '$testPass' against stored hash: " . ($result ? "✅ MATCH" : "❌ NO MATCH") . "\n";
    } else {
        echo "\nAdd ?p=yourpassword to URL to test password match\n";
    }
}

echo "</pre>";
