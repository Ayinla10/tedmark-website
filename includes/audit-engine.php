<?php
/**
 * Website Audit Engine
 * Fetches a live URL and runs a real set of technical/SEO/security/content
 * checks against it. No third-party APIs, no fabricated results.
 */

/**
 * Simple per-IP rate limiter backed by the audit_rate_limits table.
 * Returns true if the action is allowed (and records it); false if the limit was hit.
 * Fails open (allows the request) if the DB is unreachable, so a DB hiccup never
 * blocks legitimate traffic, it just means the limit isn't enforced that moment.
 */
function auditRateLimit(string $ip, string $action, int $maxCount, int $windowMinutes): bool {
    try {
        $count = fetchOne(
            "SELECT COUNT(*) AS c FROM audit_rate_limits WHERE ip_address = ? AND action = ? AND created_at > (NOW() - INTERVAL ? MINUTE)",
            [$ip, $action, $windowMinutes]
        )['c'] ?? 0;
        if ((int)$count >= $maxCount) return false;
        insert('audit_rate_limits', ['ip_address' => $ip, 'action' => $action]);
        return true;
    } catch (Exception $e) {
        return true;
    }
}

/** Block SSRF: reject anything that doesn't resolve to a public IP. */
function auditIsSafeHost(string $host): bool {
    $ip = gethostbyname($host);
    if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) return false; // DNS didn't resolve
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return false;
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

function auditNormalizeUrl(string $url): ?string {
    $url = trim($url);
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) return null;
    if (!in_array(strtolower($parts['scheme'] ?? ''), ['http','https'], true)) return null;
    if (!auditIsSafeHost($parts['host'])) return null;
    return $url;
}

function auditFetch(string $url, int $timeout = 8): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 4,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS=> CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Dest: document',
            'Upgrade-Insecure-Requests: 1',
        ],
    ]);
    $start = microtime(true);
    $raw = curl_exec($ch);
    $timeMs = (int) round((microtime(true) - $start) * 1000);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);

    if ($raw === false) {
        return ['ok'=>false, 'error'=>$err ?: 'Request failed', 'time_ms'=>$timeMs];
    }
    $headerSize = $info['header_size'] ?? 0;
    $headersRaw = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    $headers = [];
    foreach (explode("\r\n", $headersRaw) as $line) {
        if (strpos($line, ':') !== false) {
            [$k, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
    }
    return [
        'ok'         => true,
        'code'       => (int)($info['http_code'] ?? 0),
        'final_url'  => $info['url'] ?? $url,
        'headers'    => $headers,
        'body'       => $body,
        'size_bytes' => strlen($body),
        'time_ms'    => $timeMs,
    ];
}

function auditQuickHead(string $url, int $timeout = 4): int {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $code;
}

function auditCheck(string $id, string $label, string $category, string $status, string $detail, int $weight = 1): array {
    return compact('id','label','category','status','detail','weight');
}

/** Informational only, doesn't affect scoring. */
function auditDetectTech(string $html, array $headers): array {
    $tech = [];
    $patterns = [
        'WordPress' => '/wp-content|wp-includes/i', 'Shopify' => '/shopify/i', 'Wix' => '/wix\.com|wixstatic/i',
        'Squarespace' => '/squarespace/i', 'Webflow' => '/webflow/i', 'Next.js' => '/next\.js|__next/i',
        'React' => '/react|__REACT/i', 'Vue.js' => '/vue\.js|__vue/i', 'Bootstrap' => '/bootstrap/i',
        'Tailwind CSS' => '/tailwind/i',
    ];
    foreach ($patterns as $name => $pattern) { if (preg_match($pattern, $html)) $tech[] = $name; }
    $server = $headers['server'] ?? '';
    if (stripos($server, 'nginx') !== false) $tech[] = 'Nginx';
    if (stripos($server, 'apache') !== false) $tech[] = 'Apache';
    if (stripos($server, 'cloudflare') !== false || isset($headers['cf-ray'])) $tech[] = 'Cloudflare';
    return array_values(array_unique($tech));
}

function auditShortPath(string $url, string $origin): string {
    $path = substr($url, strlen($origin));
    $path = trim($path, '/');
    return $path === '' ? '/' : '/' . $path;
}

/** Pull normalized, in-scope internal links out of a parsed page. */
/** Strip a leading "www." so www/non-www are treated as the same site. */
function auditBareHost(string $host): string {
    $host = strtolower($host);
    return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
}

function auditExtractLinks(DOMXPath $xpath, string $origin, string $scheme, string $selfUrl): array {
    $found = [];
    $skipExt = ['.pdf','.jpg','.jpeg','.png','.gif','.svg','.zip','.doc','.docx','.xls','.xlsx','.mp4','.mp3','.css','.js','.ico','.webp'];
    $originBareHost = auditBareHost(parse_url($origin, PHP_URL_HOST) ?: '');
    foreach ($xpath->query('//a[@href]') as $a) {
        $href = $a->getAttribute('href');
        if (!$href || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) continue;
        $resolved = $href;
        if (str_starts_with($href, '//')) $resolved = $scheme . ':' . $href;
        elseif (str_starts_with($href, '/')) $resolved = $origin . $href;
        elseif (!preg_match('#^https?://#i', $href)) $resolved = rtrim($origin,'/') . '/' . ltrim($href,'/');
        $resolved = strtok($resolved, '#'); // drop fragments
        $resolvedHost = parse_url($resolved, PHP_URL_HOST) ?: '';
        if (auditBareHost($resolvedHost) !== $originBareHost) continue;
        // Rewrite to the origin's own host so www/non-www variants of the same
        // page collapse into one canonical URL instead of being crawled twice.
        $resolved = str_replace($resolvedHost, parse_url($origin, PHP_URL_HOST), $resolved);
        $resolved = rtrim($resolved, '/');
        if ($resolved === rtrim($selfUrl, '/')) continue;
        $lower = strtolower($resolved);
        $skip = false;
        foreach ($skipExt as $ext) { if (str_ends_with(strtok($lower,'?'), $ext)) { $skip = true; break; } }
        if ($skip) continue;
        if (!in_array($resolved, $found, true)) $found[] = $resolved;
    }
    return $found;
}

/**
 * Run the full audit. Returns ['ok'=>bool, 'error'=>?, 'score'=>int, 'checks'=>[...], 'meta'=>[...]]
 */
function runWebsiteAudit(string $inputUrl): array {
    if (function_exists('set_time_limit')) { @set_time_limit(75); }
    $url = auditNormalizeUrl($inputUrl);
    if (!$url) {
        return ['ok'=>false, 'error'=>'That URL looks invalid, or points to a private/internal address we can\'t scan.'];
    }

    $res = auditFetch($url);
    if (!$res['ok']) {
        return ['ok'=>false, 'error'=>'Could not reach that site (' . $res['error'] . '). Check the URL and try again.'];
    }
    if ($res['code'] < 200 || $res['code'] >= 400) {
        if (in_array($res['code'], [401, 403, 406, 415, 429], true)) {
            return ['ok'=>false, 'error'=>"That site's security settings ({$res['code']}) are blocking our scanner. This usually means a firewall (like Cloudflare) is set to challenge automated requests. Try again in a moment, or contact us if it keeps happening."];
        }
        return ['ok'=>false, 'error'=>"That URL returned an HTTP {$res['code']} error, so it can't be audited."];
    }

    $html = $res['body'];
    $headers = $res['headers'];
    $checks = [];

    // Detect bot-challenge / "checking your browser" interstitials (Cloudflare, etc.)
    // — these return HTTP 200 with almost no real content, so treat them as unscannable rather than
    // silently reporting a score for a fake page.
    $challengeSignals = ['checking your browser', 'one moment, please', 'just a moment', 'attention required',
        'ddos protection by', 'enable javascript and cookies', 'verifying you are human'];
    $htmlLower = strtolower($html);
    $looksLikeChallenge = strlen($html) < 15000 && array_reduce(
        $challengeSignals,
        fn($found, $signal) => $found || str_contains($htmlLower, $signal),
        false
    );
    if ($looksLikeChallenge) {
        return ['ok'=>false, 'error'=>"That site is protected by a bot-challenge (e.g. Cloudflare) that blocks automated scanners like ours — it served us a \"checking your browser\" page instead of the real site. This isn't something we can bypass; you'd need to allowlist our scanner in that site's firewall settings, or the site owner can temporarily lower the security level to scan it."];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    $parts = parse_url($res['final_url']);
    $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
    $isHttps = strtolower($parts['scheme'] ?? '') === 'https';

    // ── SEO ──────────────────────────────────────────────
    $titleNode = $xpath->query('//title')->item(0);
    $title = $titleNode ? trim($titleNode->textContent) : '';
    if (!$title) {
        $checks[] = auditCheck('title','Page Title','SEO','fail','No <title> tag found.',3);
    } elseif (strlen($title) < 15 || strlen($title) > 65) {
        $checks[] = auditCheck('title','Page Title','SEO','warn',"Title is ".strlen($title)." characters (\"$title\"). Aim for 15-65.",2);
    } else {
        $checks[] = auditCheck('title','Page Title','SEO','pass',"Good length (\"$title\").",3);
    }

    $metaDesc = '';
    foreach ($xpath->query('//meta[@name="description"]') as $m) { $metaDesc = trim($m->getAttribute('content')); break; }
    if (!$metaDesc) {
        $checks[] = auditCheck('meta_desc','Meta Description','SEO','fail','No meta description found.',3);
    } elseif (strlen($metaDesc) < 50 || strlen($metaDesc) > 160) {
        $checks[] = auditCheck('meta_desc','Meta Description','SEO','warn','Length is '.strlen($metaDesc).' characters. Aim for 50-160.',2);
    } else {
        $checks[] = auditCheck('meta_desc','Meta Description','SEO','pass','Present and well-sized.',2);
    }

    $h1s = $xpath->query('//h1');
    if ($h1s->length === 0) {
        $checks[] = auditCheck('h1','H1 Heading','SEO','fail','No H1 tag found on the page.',2);
    } elseif ($h1s->length > 1) {
        $checks[] = auditCheck('h1','H1 Heading','SEO','warn',"Found {$h1s->length} H1 tags. Ideally use exactly one.",1);
    } else {
        $checks[] = auditCheck('h1','H1 Heading','SEO','pass','Exactly one H1 tag found.',2);
    }

    $canonical = $xpath->query('//link[@rel="canonical"]')->item(0);
    $checks[] = $canonical
        ? auditCheck('canonical','Canonical Tag','SEO','pass','Canonical URL is set.',1)
        : auditCheck('canonical','Canonical Tag','SEO','warn','No canonical tag found.',1);

    $ogTags = $xpath->query('//meta[starts-with(@property,"og:")]');
    $checks[] = $ogTags->length >= 3
        ? auditCheck('opengraph','Open Graph Tags','SEO','pass',"{$ogTags->length} Open Graph tags found (social sharing previews).",1)
        : auditCheck('opengraph','Open Graph Tags','SEO','warn','Missing or incomplete Open Graph tags, link previews may look broken when shared.',1);

    $twitterCard = $xpath->query('//meta[@name="twitter:card"]')->item(0);
    $checks[] = $twitterCard
        ? auditCheck('twitter_card','Twitter Card Tag','SEO','pass','Present.',1)
        : auditCheck('twitter_card','Twitter Card Tag','SEO','warn','Missing twitter:card meta tag.',1);

    $robotsMeta = '';
    foreach ($xpath->query('//meta[@name="robots"]') as $m) { $robotsMeta = strtolower($m->getAttribute('content')); break; }
    $checks[] = (strpos($robotsMeta, 'noindex') !== false)
        ? auditCheck('robots_meta','Indexing Allowed','SEO','fail','Page has a "noindex" directive, it will be hidden from Google.',3)
        : auditCheck('robots_meta','Indexing Allowed','SEO','pass','Page is indexable.',2);

    $imgs = $xpath->query('//img');
    $imgsMissingAlt = 0;
    foreach ($imgs as $img) { if (trim($img->getAttribute('alt')) === '') $imgsMissingAlt++; }
    if ($imgs->length > 0) {
        $pct = round(100 * ($imgs->length - $imgsMissingAlt) / $imgs->length);
        $checks[] = $pct >= 90
            ? auditCheck('img_alt','Image Alt Text','SEO','pass',"$pct% of {$imgs->length} images have alt text.",2)
            : auditCheck('img_alt','Image Alt Text','SEO','warn',"Only $pct% of {$imgs->length} images have alt text ($imgsMissingAlt missing).",2);
    } else {
        $checks[] = auditCheck('img_alt','Image Alt Text','SEO','pass','No images to check.',1);
    }

    $robotsTxtCode = auditQuickHead($origin . '/robots.txt');
    $checks[] = ($robotsTxtCode >= 200 && $robotsTxtCode < 400)
        ? auditCheck('robots_txt','robots.txt','SEO','pass','robots.txt is present.',1)
        : auditCheck('robots_txt','robots.txt','SEO','warn','No robots.txt found at the site root.',1);

    $sitemapCode = auditQuickHead($origin . '/sitemap.xml');
    $checks[] = ($sitemapCode >= 200 && $sitemapCode < 400)
        ? auditCheck('sitemap','XML Sitemap','SEO','pass','sitemap.xml is present.',1)
        : auditCheck('sitemap','XML Sitemap','SEO','warn','No sitemap.xml found at the site root.',1);

    // ── Technical ────────────────────────────────────────
    $checks[] = $isHttps
        ? auditCheck('https','HTTPS Enabled','Technical','pass','Site loads securely over HTTPS.',3)
        : auditCheck('https','HTTPS Enabled','Technical','fail','Site is not served over HTTPS.',3);

    $viewport = $xpath->query('//meta[@name="viewport"]')->item(0);
    $checks[] = $viewport
        ? auditCheck('viewport','Mobile Viewport Tag','Technical','pass','Responsive viewport meta tag present.',3)
        : auditCheck('viewport','Mobile Viewport Tag','Technical','fail','No viewport meta tag, page likely isn\'t mobile-friendly.',3);

    $favicon = $xpath->query('//link[contains(@rel,"icon")]')->item(0);
    $faviconRootCode = auditQuickHead($origin . '/favicon.ico');
    $checks[] = ($favicon || ($faviconRootCode >= 200 && $faviconRootCode < 400))
        ? auditCheck('favicon','Favicon','Technical','pass','Favicon detected.',1)
        : auditCheck('favicon','Favicon','Technical','warn','No favicon detected.',1);

    $charset = $xpath->query('//meta[@charset]')->item(0);
    $checks[] = $charset
        ? auditCheck('charset','Character Encoding','Technical','pass','Charset declared.',1)
        : auditCheck('charset','Character Encoding','Technical','warn','No explicit charset meta tag found.',1);

    $doctype = stripos($html, '<!doctype html') !== false;
    $checks[] = $doctype
        ? auditCheck('doctype','HTML5 Doctype','Technical','pass','Valid HTML5 doctype declared.',1)
        : auditCheck('doctype','HTML5 Doctype','Technical','warn','Missing or non-standard doctype.',1);

    if ($res['size_bytes'] > 2_000_000) {
        $checks[] = auditCheck('page_size','Page Weight','Technical','warn','HTML document is '.round($res['size_bytes']/1_000_000,1).'MB, quite heavy.',2);
    } else {
        $checks[] = auditCheck('page_size','Page Weight','Technical','pass','HTML document size is '.round($res['size_bytes']/1024).'KB.',2);
    }

    if ($res['time_ms'] > 2500) {
        $checks[] = auditCheck('load_time','Response Time','Technical','warn',"Server took {$res['time_ms']}ms to respond, slower than ideal.",3);
    } else {
        $checks[] = auditCheck('load_time','Response Time','Technical','pass',"Server responded in {$res['time_ms']}ms.",3);
    }

    $scripts = $xpath->query('//head//script[not(@async) and not(@defer)]');
    $checks[] = $scripts->length === 0
        ? auditCheck('render_blocking','Render-Blocking Scripts','Technical','pass','No render-blocking scripts detected in <head>.',2)
        : auditCheck('render_blocking','Render-Blocking Scripts','Technical','warn',"{$scripts->length} script(s) in <head> without async/defer, may slow page rendering.",2);

    // ── Site-wide crawl (breadth-first) ─────────────────────
    // Bounded by page count AND wall-clock time so shared hosting never times out.
    $crawlStart   = microtime(true);
    $crawlBudget  = 22; // seconds
    $maxPages     = 12; // including the homepage

    $scheme = $parts['scheme'] ?? 'https';
    $visited = [rtrim($url, '/'), rtrim($res['final_url'], '/')];
    $queue = auditExtractLinks($xpath, $origin, $scheme, $res['final_url']);
    $allLinksSeen = $queue; // full inventory for dead-link checking
    $pagesScanned = 1;
    error_log("Audit crawl: origin=$origin found " . count($queue) . " initial links to crawl for $url");
    $pageReports = [];

    while (!empty($queue) && $pagesScanned < $maxPages && (microtime(true) - $crawlStart) < $crawlBudget) {
        $pageUrl = array_shift($queue);
        $normalized = rtrim($pageUrl, '/');
        if (in_array($normalized, $visited, true)) continue;
        $visited[] = $normalized;

        $pageRes = auditFetch($pageUrl, 6);
        if (!$pageRes['ok'] || $pageRes['code'] < 200 || $pageRes['code'] >= 400) {
            error_log('Audit crawl: failed to fetch ' . $pageUrl . ' — ' . ($pageRes['ok'] ? 'HTTP ' . $pageRes['code'] : ('curl error: ' . ($pageRes['error'] ?? 'unknown'))));
            $checks[] = auditCheck('page_'.md5($pageUrl), 'Page: '.auditShortPath($pageUrl,$origin), 'Additional Pages', 'fail', 'Page could not be loaded (broken link).', 2);
            $pageReports[] = [
                'path' => auditShortPath($pageUrl, $origin), 'score' => 0,
                'strengths' => ['N/A'], 'issues' => ['Page failed to load'],
                'priority_fix' => 'Fix or remove this broken link',
            ];
            continue;
        }
        $pagesScanned++;

        libxml_use_internal_errors(true);
        $pDom = new DOMDocument();
        $pDom->loadHTML('<?xml encoding="utf-8" ?>' . $pageRes['body']);
        libxml_clear_errors();
        $pXpath = new DOMXPath($pDom);

        $pTitleNode = $pXpath->query('//title')->item(0);
        $pTitle = $pTitleNode ? trim($pTitleNode->textContent) : '';
        $pMeta = '';
        foreach ($pXpath->query('//meta[@name="description"]') as $m) { $pMeta = trim($m->getAttribute('content')); break; }
        $pH1 = $pXpath->query('//h1')->length;
        $pBody = $pXpath->query('//body')->item(0);
        $pText = $pBody ? preg_replace('/\s+/', ' ', trim($pBody->textContent)) : '';
        $pWords = $pText ? str_word_count($pText) : 0;
        $pPlaceholder = null;
        foreach (['coming soon','lorem ipsum','under construction','placeholder text'] as $pp) {
            if (stripos($pText, $pp) !== false) { $pPlaceholder = $pp; break; }
        }

        $label = 'Page: ' . auditShortPath($pageUrl, $origin);
        $issues = [];
        if (!$pTitle) $issues[] = 'missing title';
        if (!$pMeta) $issues[] = 'missing meta description';
        if ($pH1 === 0) $issues[] = 'no H1';
        if ($pWords < 100) $issues[] = 'thin content (~'.$pWords.' words)';
        if ($pPlaceholder) $issues[] = "placeholder text (\"$pPlaceholder\")";

        if (empty($issues)) {
            $checks[] = auditCheck('page_'.md5($pageUrl), $label, 'Additional Pages', 'pass', "All good: title, meta description, H1 present, ~$pWords words.", 2);
        } else {
            $status = $pPlaceholder ? 'fail' : 'warn';
            $checks[] = auditCheck('page_'.md5($pageUrl), $label, 'Additional Pages', $status, ucfirst(implode(', ', $issues)).'.', $pPlaceholder ? 3 : 2);
        }

        $pStrengths = [];
        $pTitle ? $pStrengths[] = 'Title present' : null;
        $pMeta ? $pStrengths[] = 'Meta description present' : null;
        $pH1 === 1 ? $pStrengths[] = 'Clean H1 structure' : null;
        $pWords >= 100 ? $pStrengths[] = "Good content depth (~$pWords words)" : null;
        $pIssuesReadable = array_map('ucfirst', $issues);
        $pScore = (int) round(100 * count($pStrengths) / max(1, count($pStrengths) + count($issues)));
        $pageReports[] = [
            'path' => auditShortPath($pageUrl, $origin), 'score' => $pScore,
            'strengths' => $pStrengths ?: ['No major strengths found'],
            'issues' => $pIssuesReadable ?: ['No major issues found'],
            'priority_fix' => $pIssuesReadable[0] ?? 'Keep up the good work',
        ];

        // queue up this page's own links for further crawling
        $pageLinks = auditExtractLinks($pXpath, $origin, $scheme, $pageUrl);
        foreach ($pageLinks as $pl) {
            if (!in_array($pl, $allLinksSeen, true)) $allLinksSeen[] = $pl;
            if (!in_array(rtrim($pl,'/'), $visited, true) && !in_array($pl, $queue, true)) $queue[] = $pl;
        }
    }

    $crawlTruncated = !empty($queue) && $pagesScanned >= $maxPages;

    // ── Site-wide dead-link check (every unique internal link found, not a sample) ──
    $linkCheckStart = microtime(true);
    $linkCheckBudget = 18; // seconds
    $linksToCheck = array_slice($allLinksSeen, 0, 60);
    $brokenLinks = [];
    $checkedCount = 0;
    foreach ($linksToCheck as $link) {
        if ((microtime(true) - $linkCheckStart) > $linkCheckBudget) break;
        $checkedCount++;
        $code = auditQuickHead($link, 4);
        if ($code === 0 || $code >= 400) $brokenLinks[] = ['url' => $link, 'code' => $code];
    }
    if ($checkedCount > 0) {
        if (empty($brokenLinks)) {
            $checks[] = auditCheck('broken_links', 'Dead URLs (Site-Wide)', 'Technical', 'pass', "Checked all $checkedCount internal links found across $pagesScanned pages, none were broken.", 3);
        } else {
            $examples = array_slice(array_map(fn($b) => auditShortPath($b['url'], $origin) . ' (' . ($b['code'] ?: 'no response') . ')', $brokenLinks), 0, 6);
            $checks[] = auditCheck('broken_links', 'Dead URLs (Site-Wide)', 'Technical', 'fail',
                count($brokenLinks) . " of $checkedCount internal links are broken: " . implode(', ', $examples) . (count($brokenLinks) > 6 ? ', and more' : '') . '.', 3);
        }
    }

    // ── Security ─────────────────────────────────────────
    $secHeaders = [
        'strict-transport-security' => 'HSTS (Strict-Transport-Security)',
        'x-content-type-options'    => 'X-Content-Type-Options',
        'x-frame-options'           => 'X-Frame-Options / Clickjacking Protection',
        'content-security-policy'   => 'Content-Security-Policy',
    ];
    foreach ($secHeaders as $key => $label) {
        $checks[] = isset($headers[$key])
            ? auditCheck('hdr_'.$key, $label, 'Security', 'pass', 'Header is set.', 1)
            : auditCheck('hdr_'.$key, $label, 'Security', 'warn', 'Header is missing.', 1);
    }
    $checks[] = isset($headers['server'])
        ? auditCheck('server_expose','Server Info Exposure','Security','warn','Server header reveals: "'.$headers['server'].'" (minor info leak).',1)
        : auditCheck('server_expose','Server Info Exposure','Security','pass','No Server header leaking software details.',1);

    // ── Content ──────────────────────────────────────────
    $bodyNode = $xpath->query('//body')->item(0);
    $textContent = $bodyNode ? preg_replace('/\s+/', ' ', trim($bodyNode->textContent)) : '';
    $wordCount = $textContent ? str_word_count($textContent) : 0;
    $checks[] = $wordCount >= 200
        ? auditCheck('word_count','Content Length','Content','pass',"Homepage has about $wordCount words.",1)
        : auditCheck('word_count','Content Length','Content','warn',"Only about $wordCount words on the homepage, thin content can hurt SEO.",1);

    $hasPhoneOrEmail = (bool) preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+|\+?\d[\d\s().-]{7,}\d/', $textContent);
    $checks[] = $hasPhoneOrEmail
        ? auditCheck('contact_info','Visible Contact Info','Content','pass','Found a phone number or email address on the page.',1)
        : auditCheck('contact_info','Visible Contact Info','Content','warn','No obvious phone number or email address found on the page.',1);

    $placeholderPatterns = ['coming soon', 'lorem ipsum', 'under construction', 'placeholder text'];
    $foundPlaceholder = null;
    foreach ($placeholderPatterns as $p) { if (stripos($textContent, $p) !== false) { $foundPlaceholder = $p; break; } }
    $checks[] = $foundPlaceholder
        ? auditCheck('placeholder_content','Placeholder Content','Content','fail',"Found placeholder text (\"$foundPlaceholder\") still live on the page.",3)
        : auditCheck('placeholder_content','Placeholder Content','Content','pass','No leftover placeholder or "coming soon" text found.',2);

    // ── Broken / dead-end links (href="#" on real nav/footer links) ──
    $deadHrefNodes = $xpath->query('//a[@href="#"]');
    $deadHrefCount = $deadHrefNodes->length;
    if ($deadHrefCount > 0) {
        $sampleLabels = [];
        foreach ($deadHrefNodes as $a) {
            $label = trim($a->textContent) ?: trim($a->getAttribute('aria-label')) ?: ($a->getElementsByTagName('i')->length ? 'icon link' : 'link');
            if (!in_array($label, $sampleLabels, true)) $sampleLabels[] = $label;
            if (count($sampleLabels) >= 5) break;
        }
        $checks[] = auditCheck('dead_links', 'Dead-End Links', 'Technical', 'fail',
            "$deadHrefCount link(s) point to \"#\" and go nowhere (e.g. " . implode(', ', $sampleLabels) . "). These usually indicate unfinished social/legal links.", 3);
    } else {
        $checks[] = auditCheck('dead_links', 'Dead-End Links', 'Technical', 'pass', 'No links pointing to "#" found.', 2);
    }

    // ── Structured data (schema.org) ────────────────────────
    $jsonLd = $xpath->query('//script[@type="application/ld+json"]');
    $microdata = $xpath->query('//*[@itemscope]');
    $checks[] = ($jsonLd->length > 0 || $microdata->length > 0)
        ? auditCheck('schema', 'Structured Data (Schema.org)', 'SEO', 'pass', ($jsonLd->length + $microdata->length) . ' structured data block(s) found.', 2)
        : auditCheck('schema', 'Structured Data (Schema.org)', 'SEO', 'fail', 'No JSON-LD or Microdata found. Missing rich-result opportunities (Organization, LocalBusiness, FAQ, etc.).', 2);

    // ── UX ───────────────────────────────────────────────
    $formCount = $xpath->query('//form')->length;
    $checks[] = $formCount > 0
        ? auditCheck('forms', 'Forms Present', 'UX', 'pass', "$formCount form(s) found for lead capture.", 1)
        : auditCheck('forms', 'Forms Present', 'UX', 'warn', 'No forms found, visitors have no easy way to submit an inquiry.', 2);

    $hasWhatsApp = (bool) preg_match('/wa\.me\/|whatsapp\.com\/send|whatsapp-button/i', $html);
    $hasLiveChat = (bool) preg_match('/intercom|tawk\.to|crisp\.chat|tidio|freshchat/i', $html);
    $checks[] = ($hasWhatsApp || $hasLiveChat)
        ? auditCheck('chat_widget', 'Live Chat / WhatsApp', 'UX', 'pass', $hasWhatsApp ? 'WhatsApp contact link detected.' : 'Live chat widget detected.', 1)
        : auditCheck('chat_widget', 'Live Chat / WhatsApp', 'UX', 'warn', 'No WhatsApp link or live chat widget detected, a low-friction contact option many visitors expect.', 1);

    $navCount = $xpath->query('//nav')->length;
    $checks[] = $navCount > 0
        ? auditCheck('nav_present', 'Navigation Structure', 'UX', 'pass', 'Semantic <nav> element found.', 1)
        : auditCheck('nav_present', 'Navigation Structure', 'UX', 'warn', 'No semantic <nav> element found, may hurt accessibility and SEO crawlability.', 1);

    // ── Conversion ───────────────────────────────────────
    $ctaKeywords = ['book a call','get started','contact us','free consultation','request a quote','sign up','subscribe','call now','download'];
    $ctasFound = [];
    foreach ($ctaKeywords as $kw) { if (stripos($textContent, $kw) !== false) $ctasFound[] = $kw; }
    $checks[] = !empty($ctasFound)
        ? auditCheck('cta_presence', 'Call-to-Action Presence', 'Conversion', 'pass', count($ctasFound).' CTA phrase(s) found: '.implode(', ', array_slice($ctasFound,0,4)).'.', 3)
        : auditCheck('cta_presence', 'Call-to-Action Presence', 'Conversion', 'fail', 'No clear call-to-action phrases found on the page.', 3);

    $hasPricing = (bool) preg_match('/pricing|packages|plans|\$\d|GHS\s?\d|cedis/i', $textContent);
    $checks[] = $hasPricing
        ? auditCheck('pricing_visible', 'Pricing Transparency', 'Conversion', 'pass', 'Pricing or package information is visible.', 1)
        : auditCheck('pricing_visible', 'Pricing Transparency', 'Conversion', 'warn', 'No pricing or package information visible, visitors must contact you before knowing if you fit their budget.', 1);

    $hasTestimonials = (bool) preg_match('/testimonial|review|what.{0,15}(clients|customers) say/i', $textContent);
    $checks[] = $hasTestimonials
        ? auditCheck('testimonials', 'Testimonials Present', 'Conversion', 'pass', 'Testimonial or review content found.', 1)
        : auditCheck('testimonials', 'Testimonials Present', 'Conversion', 'warn', 'No testimonials or reviews found, a missed trust-building opportunity.', 1);

    $hasCaseStudy = (bool) preg_match('/case study|case studies|success story/i', $textContent);
    $checks[] = $hasCaseStudy
        ? auditCheck('case_studies', 'Case Studies Present', 'Conversion', 'pass', 'Case study content found.', 1)
        : auditCheck('case_studies', 'Case Studies Present', 'Conversion', 'warn', 'No case studies found, detailed proof of results helps close leads.', 1);

    $hasAnalytics = (bool) preg_match('/gtag\(|google-analytics\.com|googletagmanager/i', $html);
    $checks[] = $hasAnalytics
        ? auditCheck('analytics', 'Analytics Tracking', 'Conversion', 'pass', 'Google Analytics/Tag Manager detected.', 1)
        : auditCheck('analytics', 'Analytics Tracking', 'Conversion', 'warn', 'No Google Analytics or Tag Manager detected, you can\'t measure what you don\'t track.', 1);

    // ── Authority ────────────────────────────────────────
    $privacyCode = auditQuickHead($origin . '/privacy-policy');
    if ($privacyCode < 200 || $privacyCode >= 400) $privacyCode = auditQuickHead($origin . '/privacy');
    $termsCode = auditQuickHead($origin . '/terms-of-service');
    if ($termsCode < 200 || $termsCode >= 400) $termsCode = auditQuickHead($origin . '/terms');
    $legalOk = ($privacyCode >= 200 && $privacyCode < 400) && ($termsCode >= 200 && $termsCode < 400);
    $checks[] = $legalOk
        ? auditCheck('legal_pages', 'Legal Pages (Privacy/Terms)', 'Authority', 'pass', 'Privacy Policy and Terms of Service pages both found.', 2)
        : auditCheck('legal_pages', 'Legal Pages (Privacy/Terms)', 'Authority', 'fail', 'Privacy Policy and/or Terms of Service page missing or unreachable, a compliance and trust gap.', 2);

    $socialPlatforms = [
        'Facebook'  => '/facebook\.com\/(?!sharer)[^"\'\s#]+/i',
        'Instagram' => '/instagram\.com\/[^"\'\s#]+/i',
        'LinkedIn'  => '/linkedin\.com\/(company|in)\/[^"\'\s#]+/i',
        'Twitter/X' => '/(twitter|x)\.com\/[^"\'\s#]+/i',
        'YouTube'   => '/youtube\.com\/(channel|c|@)[^"\'\s#]+/i',
    ];
    $socialFound = [];
    foreach ($socialPlatforms as $name => $pattern) { if (preg_match($pattern, $html)) $socialFound[] = $name; }
    $checks[] = count($socialFound) >= 2
        ? auditCheck('social_presence', 'Social Media Presence', 'Authority', 'pass', 'Real links found for: '.implode(', ', $socialFound).'.', 2)
        : auditCheck('social_presence', 'Social Media Presence', 'Authority', 'warn', count($socialFound) === 0 ? 'No working social media links found.' : 'Only found: '.implode(', ', $socialFound).'. Most agencies benefit from a stronger multi-platform presence.', 2);

    // Homepage's own row in the page-by-page report (reuses checks already computed above)
    $homeStrengths = []; $homeIssues = [];
    $title ? $homeStrengths[] = 'Good page title' : $homeIssues[] = 'Missing/weak title';
    $metaDesc ? $homeStrengths[] = 'Meta description present' : $homeIssues[] = 'Missing meta description';
    $h1s->length === 1 ? $homeStrengths[] = 'Clean H1 structure' : $homeIssues[] = 'H1 tag missing or duplicated';
    $wordCount >= 200 ? $homeStrengths[] = 'Good content depth' : $homeIssues[] = "Thin content (~$wordCount words)";
    ($jsonLd->length > 0 || $microdata->length > 0) ? $homeStrengths[] = 'Structured data present' : $homeIssues[] = 'No schema markup';
    $deadHrefCount > 0 ? $homeIssues[] = "$deadHrefCount dead link(s) (href=\"#\")" : $homeStrengths[] = 'No dead-end links';
    if ($foundPlaceholder) $homeIssues[] = 'Placeholder text still live';
    $homeScore = (int) round(100 * count($homeStrengths) / max(1, count($homeStrengths) + count($homeIssues)));
    array_unshift($pageReports, [
        'path' => '/ (Homepage)', 'score' => $homeScore,
        'strengths' => $homeStrengths ?: ['No major strengths found'],
        'issues' => $homeIssues ?: ['No major issues found'],
        'priority_fix' => $homeIssues[0] ?? 'Keep up the good work',
    ]);

    // ── Score (overall + per category) ─────────────────────
    $score = auditScoreChecks($checks);
    $categoryScores = [];
    $byCategory = [];
    foreach ($checks as $c) { $byCategory[$c['category']][] = $c; }
    foreach ($byCategory as $catName => $catChecks) {
        $categoryScores[$catName] = auditScoreChecks($catChecks);
    }

    return [
        'ok'        => true,
        'url'       => $res['final_url'],
        'score'     => $score,
        'category_scores' => $categoryScores,
        'checks'    => $checks,
        'page_title'  => $title,
        'meta_desc'   => $metaDesc,
        'word_count'  => $wordCount,
        'pages_scanned' => $pagesScanned,
        'page_reports' => $pageReports,
        'technology_stack' => auditDetectTech($html, $headers),
        'pages_discovered' => count($visited) + count($queue),
        'crawl_truncated' => $crawlTruncated,
        'links_checked' => $checkedCount ?? 0,
        'links_broken'  => count($brokenLinks ?? []),
        'meta'      => ['time_ms' => $res['time_ms'], 'size_bytes' => $res['size_bytes'], 'checked_at' => date('c')],
    ];
}

function auditScoreChecks(array $checks): int {
    $totalWeight = array_sum(array_column($checks, 'weight'));
    $earned = 0;
    foreach ($checks as $c) {
        if ($c['status'] === 'pass') $earned += $c['weight'];
        elseif ($c['status'] === 'warn') $earned += $c['weight'] * 0.5;
    }
    return $totalWeight > 0 ? (int) round(100 * $earned / $totalWeight) : 0;
}
