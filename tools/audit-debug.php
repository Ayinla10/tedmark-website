<?php
/**
 * TEMPORARY debug script. Not linked anywhere.
 * Visit: https://tedmarkdigital.com/tools/audit-debug.php?url=https://brancom.co&key=tm-debug-2026
 * Delete this file once the crawl issue is diagnosed.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/audit-engine.php';

header('Content-Type: text/plain; charset=utf-8');

if (($_GET['key'] ?? '') !== 'tm-debug-2026') {
    http_response_code(403);
    echo "Forbidden.";
    exit;
}

$url = trim($_GET['url'] ?? '');
if ($url === '') {
    echo "Usage: audit-debug.php?url=https://example.com&key=tm-debug-2026";
    exit;
}

echo "=== Step 1: raw fetch of homepage ===\n";
$res = auditFetch($url);
echo "ok: " . ($res['ok'] ? 'yes' : 'no') . "\n";
echo "http_code: " . ($res['code'] ?? 'n/a') . "\n";
echo "final_url: " . ($res['final_url'] ?? 'n/a') . "\n";
echo "curl_error: " . ($res['error'] ?? 'none') . "\n";
echo "html_length: " . strlen($res['body'] ?? '') . "\n";
$snippet = substr($res['body'] ?? '', 0, 600);
echo "html_snippet:\n---\n$snippet\n---\n\n";

if (!empty($res['ok']) && !empty($res['body'])) {
    echo "=== Step 2: link extraction from that HTML ===\n";
    $dom = new DOMDocument();
    @$dom->loadHTML($res['body']);
    $xpath = new DOMXPath($dom);
    $scheme = parse_url($res['final_url'], PHP_URL_SCHEME) ?: 'https';
    $origin = $scheme . '://' . parse_url($res['final_url'], PHP_URL_HOST);
    $links = auditExtractLinks($xpath, $origin, $scheme, $res['final_url']);
    echo "origin: $origin\n";
    echo "links_found: " . count($links) . "\n";
    foreach (array_slice($links, 0, 20) as $l) echo " - $l\n";
    echo "\n";
}

echo "=== Step 3: full runWebsiteAudit() ===\n";
$audit = runWebsiteAudit($url);
echo "ok: " . ($audit['ok'] ? 'yes' : 'no') . "\n";
if (!$audit['ok']) {
    echo "error: " . ($audit['error'] ?? 'unknown') . "\n";
} else {
    echo "pages_scanned: " . ($audit['pages_scanned'] ?? 'n/a') . "\n";
    echo "pages_discovered: " . ($audit['pages_discovered'] ?? 'n/a') . "\n";
    echo "crawl_truncated: " . (!empty($audit['crawl_truncated']) ? 'yes' : 'no') . "\n";
    echo "links_checked: " . ($audit['links_checked'] ?? 'n/a') . "\n";
    echo "links_broken: " . ($audit['links_broken'] ?? 'n/a') . "\n";
}

echo "\n=== Step 4: DeepSeek key check ===\n";
echo "DEEPSEEK_API_KEY defined: " . (defined('DEEPSEEK_API_KEY') ? 'yes' : 'no') . "\n";
echo "DEEPSEEK_API_KEY non-empty: " . ((defined('DEEPSEEK_API_KEY') && DEEPSEEK_API_KEY !== '') ? 'yes' : 'no') . "\n";
if (defined('DEEPSEEK_API_KEY') && DEEPSEEK_API_KEY !== '') {
    echo "key_prefix: " . substr(DEEPSEEK_API_KEY, 0, 6) . "...\n";
}
