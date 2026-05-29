<?php
/**
 * Dynamic XML Sitemap — Tedmark Digital Agency
 * URL: /sitemap.xml.php
 * Submit to: Google Search Console & Bing Webmaster Tools
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$base = rtrim(SITE_URL, '/');
$now  = date('Y-m-d');

// Static pages with priorities
$staticPages = [
    ['url' => '/',               'priority' => '1.0', 'freq' => 'weekly'],
    ['url' => '/about.php',      'priority' => '0.8', 'freq' => 'monthly'],
    ['url' => '/services.php',   'priority' => '0.9', 'freq' => 'monthly'],
    ['url' => '/portfolio.php',  'priority' => '0.8', 'freq' => 'weekly'],
    ['url' => '/blog.php',       'priority' => '0.8', 'freq' => 'daily'],
    ['url' => '/contact.php',    'priority' => '0.7', 'freq' => 'monthly'],
    ['url' => '/industries.php', 'priority' => '0.7', 'freq' => 'monthly'],
    ['url' => '/resources.php',  'priority' => '0.6', 'freq' => 'weekly'],
    ['url' => '/solutions.php',  'priority' => '0.7', 'freq' => 'monthly'],
    ['url' => '/consultation.php','priority'=> '0.8', 'freq' => 'monthly'],
];

// Blog posts
try {
    $posts = fetchAll("SELECT slug, published_at, updated_at FROM posts WHERE status='published' ORDER BY published_at DESC");
} catch(Exception $e) { $posts = []; }

// Projects
try {
    $projects = fetchAll("SELECT slug, updated_at FROM projects WHERE status='active' ORDER BY sort_order ASC");
} catch(Exception $e) { $projects = []; }

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

// Static pages
foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base . $page['url']) . "</loc>\n";
    echo "    <lastmod>{$now}</lastmod>\n";
    echo "    <changefreq>{$page['freq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

// Blog posts
foreach ($posts as $p) {
    $lastmod = date('Y-m-d', strtotime($p['updated_at'] ?? $p['published_at']));
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base . '/blog-post.php?slug=' . rawurlencode($p['slug'])) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
}

// Projects
foreach ($projects as $pr) {
    $lastmod = date('Y-m-d', strtotime($pr['updated_at']));
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base . '/portfolio-item.php?slug=' . rawurlencode($pr['slug'])) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.6</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
