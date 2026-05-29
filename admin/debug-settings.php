<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "<pre style='background:#1e293b;color:#e2e8f0;padding:20px;font-size:13px;'>";

// Test 1: Can we read settings?
echo "=== READ TEST ===\n";
$rows = fetchAll("SELECT `key`, `value` FROM settings LIMIT 5");
echo "Rows found: " . count($rows) . "\n";
foreach ($rows as $r) echo "  {$r['key']} = {$r['value']}\n";

// Test 2: Can we write?
echo "\n=== WRITE TEST ===\n";
try {
    $existing = fetchOne("SELECT id FROM settings WHERE `key` = 'test_key'");
    if ($existing) {
        query("UPDATE settings SET `value` = ? WHERE `key` = ?", ['test_'.time(), 'test_key']);
        echo "UPDATE: OK\n";
    } else {
        query("INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, 'general')", ['test_key', 'test_'.time()]);
        echo "INSERT: OK\n";
    }
    $check = fetchOne("SELECT `value` FROM settings WHERE `key` = 'test_key'");
    echo "Saved value: " . $check['value'] . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test 3: POST simulation
echo "\n=== POST DATA TEST ===\n";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST received\n";
    echo "settings array: "; print_r($_POST['settings'] ?? 'NOT FOUND');
} else {
    echo "No POST — submit the form below to test\n";
}

echo "</pre>";
?>
<form method="POST" style="padding:20px;background:#0b1528;">
  <input type="hidden" name="settings[test_field]" value="hello123">
  <button type="submit" style="background:#22c55e;color:#000;padding:10px 20px;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Test POST</button>
</form>
