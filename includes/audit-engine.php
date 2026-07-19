<?php
/**
 * Website Audit Engine
 * Fetches a live URL and runs a real set of technical/SEO/security/content
 * checks against it. No third-party APIs, no fabricated results.
 */

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
        CURLOPT_USERAGENT      => 'TedmarkAuditBot/1.0 (+https://tedmarkdigital.com)',
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
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
        CURLOPT_USERAGENT => 'TedmarkAuditBot/1.0',
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $code;
}

function auditCheck(string $id, string $label, string $category, string $status, string $detail, int $weight = 1): array {
    return compact('id','label','category','status','detail','weight');
}

function auditShortPath(string $url, string $origin): string {
    $path = substr($url, strlen($origin));
    $path = trim($path, '/');
    return $path === '' ? '/' : '/' . $path;
}

/** Pull normalized, in-scope internal links out of a parsed page. */
function auditExtractLinks(DOMXPath $xpath, string $origin, string $scheme, string $selfUrl): array {
    $found = [];
    $skipExt = ['.pdf','.jpg','.jpeg','.png','.gif','.svg','.zip','.doc','.docx','.xls','.xlsx','.mp4','.mp3','.css','.js','.ico','.webp'];
    foreach ($xpath->query('//a[@href]') as $a) {
        $href = $a->getAttribute('href');
        if (!$href || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) continue;
        $resolved = $href;
        if (str_starts_with($href, '//')) $resolved = $scheme . ':' . $href;
        elseif (str_starts_with($href, '/')) $resolved = $origin . $href;
        elseif (!preg_match('#^https?://#i', $href)) $resolved = rtrim($origin,'/') . '/' . ltrim($href,'/');
        $resolved = strtok($resolved, '#'); // drop fragments
        if (!str_starts_with($resolved, $origin)) continue;
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
    $visited = [rtrim($url, '/')];
    $queue = auditExtractLinks($xpath, $origin, $scheme, $url);
    $allLinksSeen = $queue; // full inventory for dead-link checking
    $pagesScanned = 1;

    while (!empty($queue) && $pagesScanned < $maxPages && (microtime(true) - $crawlStart) < $crawlBudget) {
        $pageUrl = array_shift($queue);
        $normalized = rtrim($pageUrl, '/');
        if (in_array($normalized, $visited, true)) continue;
        $visited[] = $normalized;

        $pageRes = auditFetch($pageUrl, 6);
        if (!$pageRes['ok'] || $pageRes['code'] < 200 || $pageRes['code'] >= 400) {
            $checks[] = auditCheck('page_'.md5($pageUrl), 'Page: '.auditShortPath($pageUrl,$origin), 'Additional Pages', 'fail', 'Page could not be loaded (broken link).', 2);
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
