<?php
/**
 * Tedmark Digital Agency — SEO Engine
 * ─────────────────────────────────────────────────────────────
 * Usage (before require_once header.php in any page):
 *
 *   $seoData = ['page' => 'home'];          // static page
 *   $seoData = ['post' => $postArray];      // blog post
 *   $seoData = ['project' => $projArray];   // portfolio project
 *   $seoData = ['title' => '...', 'description' => '...'];  // manual override
 *
 * Then in header.php, renderSeoTags($cfg, $seoData) is called.
 */

/**
 * Build the full SEO meta array for a page.
 */
function buildSeoMeta(array $cfg, array $opts = []): array {
    $siteUrl   = defined('SITE_URL') ? SITE_URL : '';
    $siteName  = $cfg['site_name'] ?? 'Tedmark Digital Agency';
    $titleTpl  = $cfg['seo_title_template'] ?? '{title} | ' . $siteName;
    $defDesc   = $cfg['seo_default_description'] ?? ($cfg['site_tagline'] ?? '');
    $defImg    = ($cfg['seo_default_og_image'] ?? '') ?: $siteUrl . '/assets/images/og-default.jpg';
    $defKws    = $cfg['seo_default_keywords'] ?? 'digital agency Ghana, web development Accra';
    $twHandle  = $cfg['seo_twitter_handle'] ?? '';

    $meta = [
        'raw_title'   => '',
        'description' => $defDesc,
        'keywords'    => $defKws,
        'og_image'    => $defImg,
        'og_type'     => 'website',
        'canonical'   => rtrim($siteUrl, '/') . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'),
        'no_index'    => false,
        'article'     => null,
        'site_name'   => $siteName,
        'site_url'    => $siteUrl,
        'tw_handle'   => $twHandle,
        'cfg'         => $cfg,
    ];

    // ── Per-page SEO from seo_pages table ─────────────────────
    if (!empty($opts['page'])) {
        try {
            $sp = fetchOne("SELECT * FROM seo_pages WHERE page_key = ?", [$opts['page']]);
            if ($sp) {
                if ($sp['meta_title'])       $meta['raw_title']   = $sp['meta_title'];
                if ($sp['meta_description']) $meta['description'] = $sp['meta_description'];
                if ($sp['meta_keywords'])    $meta['keywords']    = $sp['meta_keywords'];
                if ($sp['og_image'])         $meta['og_image']    = $sp['og_image'];
                if ($sp['canonical_url'])    $meta['canonical']   = $sp['canonical_url'];
                if ($sp['no_index'])         $meta['no_index']    = true;
            }
        } catch(Exception $e) {}
    }

    // ── Blog post SEO ──────────────────────────────────────────
    if (!empty($opts['post'])) {
        $p = $opts['post'];
        $meta['og_type']   = 'article';
        $meta['raw_title'] = $p['seo_title'] ?: $p['title'];
        $meta['description'] = $p['seo_description'] ?: ($p['excerpt'] ?: $meta['description']);
        $meta['og_image']  = $p['og_image'] ?: ($p['featured_image'] ?: $defImg);
        if (!empty($p['canonical_url'])) $meta['canonical'] = $p['canonical_url'];
        if (!empty($p['no_index']))      $meta['no_index']  = true;
        $meta['article'] = $p;
    }

    // ── Project SEO ────────────────────────────────────────────
    if (!empty($opts['project'])) {
        $pr = $opts['project'];
        $meta['raw_title']   = $pr['seo_title'] ?: $pr['title'];
        $meta['description'] = $pr['seo_description'] ?: (substr(strip_tags($pr['description'] ?? ''), 0, 160) ?: $meta['description']);
        $meta['og_image']    = $pr['og_image'] ?: ($pr['cover_image'] ?: $defImg);
    }

    // ── Direct overrides ──────────────────────────────────────
    if (!empty($opts['title']))       $meta['raw_title']   = $opts['title'];
    if (!empty($opts['description'])) $meta['description'] = $opts['description'];
    if (!empty($opts['keywords']))    $meta['keywords']    = $opts['keywords'];
    if (!empty($opts['og_image']))    $meta['og_image']    = $opts['og_image'];
    if (!empty($opts['canonical']))   $meta['canonical']   = $opts['canonical'];
    if (!empty($opts['og_type']))     $meta['og_type']     = $opts['og_type'];
    if (isset($opts['no_index']))     $meta['no_index']    = $opts['no_index'];

    // Fallback raw_title
    if (!$meta['raw_title'] && !empty($opts['page'])) {
        $labels = ['home'=>'Home','about'=>'About Us','services'=>'Our Services',
                   'portfolio'=>'Portfolio','blog'=>'Blog','contact'=>'Contact Us',
                   'industries'=>'Industries We Serve','resources'=>'Free Resources'];
        $meta['raw_title'] = $labels[$opts['page']] ?? ucfirst($opts['page']);
    }

    // Build final <title>
    if ($meta['raw_title']) {
        $meta['title'] = str_replace('{title}', $meta['raw_title'], $titleTpl);
    } else {
        $meta['title'] = $siteName . ' — ' . ($cfg['site_tagline'] ?? 'Digital Agency Ghana');
    }

    // Trim description to 160 chars
    $meta['description'] = mb_substr(strip_tags($meta['description']), 0, 160);

    return $meta;
}

/**
 * Output all <head> SEO tags: title, meta, OG, Twitter, JSON-LD.
 * Call this inside <head> from header.php.
 */
function renderSeoTags(array $cfg, array $opts = []): void {
    $m = buildSeoMeta($cfg, $opts);
    $robots = $m['no_index'] ? 'noindex,nofollow' : 'index,follow';
    $ga4    = $cfg['seo_ga4_id'] ?? '';
    $gsc    = $cfg['seo_gsc_verification'] ?? '';
    $bing   = $cfg['seo_bing_verification'] ?? '';

    // ── Primary meta ──────────────────────────────────────────
    echo "\n<!-- ⚡ SEO by Tedmark CMS -->\n";
    echo '<title>' . htmlspecialchars($m['title']) . "</title>\n";
    echo '<meta name="description" content="' . htmlspecialchars($m['description']) . "\">\n";
    if ($m['keywords']) echo '<meta name="keywords" content="' . htmlspecialchars($m['keywords']) . "\">\n";
    echo '<meta name="robots" content="' . $robots . "\">\n";
    echo '<link rel="canonical" href="' . htmlspecialchars($m['canonical']) . "\">\n";

    // ── Geo / Language signals (Ghana ranking boost) ──────────
    echo '<meta name="geo.region" content="GH-AA">' . "\n";
    echo '<meta name="geo.placename" content="' . htmlspecialchars($cfg['schema_city'] ?? 'Accra') . ', Ghana">' . "\n";
    echo '<meta name="ICBM" content="' . ($cfg['schema_lat'] ?? '5.6037') . ', ' . ($cfg['schema_lng'] ?? '-0.1870') . "\">\n";
    echo '<link rel="alternate" hreflang="en-GH" href="' . htmlspecialchars($m['canonical']) . "\">\n";
    echo '<link rel="alternate" hreflang="en" href="' . htmlspecialchars($m['canonical']) . "\">\n";
    echo '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($m['site_url']) . "\">\n";

    // ── Verification tags ─────────────────────────────────────
    if ($gsc)  echo '<meta name="google-site-verification" content="' . htmlspecialchars($gsc) . "\">\n";
    if ($bing) echo '<meta name="msvalidate.01" content="' . htmlspecialchars($bing) . "\">\n";

    // ── Open Graph ────────────────────────────────────────────
    echo "\n<!-- Open Graph -->\n";
    echo '<meta property="og:type" content="' . htmlspecialchars($m['og_type']) . "\">\n";
    echo '<meta property="og:title" content="' . htmlspecialchars($m['title']) . "\">\n";
    echo '<meta property="og:description" content="' . htmlspecialchars($m['description']) . "\">\n";
    echo '<meta property="og:url" content="' . htmlspecialchars($m['canonical']) . "\">\n";
    echo '<meta property="og:site_name" content="' . htmlspecialchars($m['site_name']) . "\">\n";
    echo '<meta property="og:locale" content="en_GH">' . "\n";
    if ($m['og_image']) {
        echo '<meta property="og:image" content="' . htmlspecialchars($m['og_image']) . "\">\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
        echo '<meta property="og:image:alt" content="' . htmlspecialchars($m['title']) . "\">\n";
    }

    // ── Twitter Card ──────────────────────────────────────────
    echo "\n<!-- Twitter Card -->\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . htmlspecialchars($m['title']) . "\">\n";
    echo '<meta name="twitter:description" content="' . htmlspecialchars($m['description']) . "\">\n";
    if ($m['og_image']) echo '<meta name="twitter:image" content="' . htmlspecialchars($m['og_image']) . "\">\n";
    if ($m['tw_handle']) echo '<meta name="twitter:site" content="' . htmlspecialchars($m['tw_handle']) . "\">\n";

    // ── JSON-LD Structured Data ────────────────────────────────
    _renderJsonLd($m);

    // ── Google Analytics 4 ────────────────────────────────────
    if ($ga4): ?>

<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= htmlspecialchars($ga4) ?>', { 'anonymize_ip': true });
</script>
<?php endif;

    echo "<!-- / SEO -->\n";
}

/**
 * Output JSON-LD structured data blocks.
 */
function _renderJsonLd(array $m): void {
    $cfg     = $m['cfg'];
    $siteUrl = $m['site_url'];
    $siteName = $m['site_name'];

    $schemas = [];

    // ── WebSite with Sitelinks SearchBox ──────────────────────
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => $siteName,
        'url'      => $siteUrl,
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => ['@type'=>'EntryPoint','urlTemplate'=> $siteUrl . '/blog.php?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    // ── LocalBusiness / ProfessionalService ───────────────────
    $socialLinks = array_values(array_filter([
        $cfg['social_facebook']  ?? '',
        $cfg['social_linkedin']  ?? '',
        $cfg['social_twitter']   ?? '',
        $cfg['social_instagram'] ?? '',
    ], fn($v) => $v && $v !== '#' && strpos($v,'http') === 0));

    $business = [
        '@context'    => 'https://schema.org',
        '@type'       => ['ProfessionalService','LocalBusiness'],
        '@id'         => $siteUrl . '/#organization',
        'name'        => $siteName,
        'url'         => $siteUrl,
        'logo'        => [
            '@type' => 'ImageObject',
            'url'   => $siteUrl . '/assets/images/tedmark logo copy2.png',
        ],
        'image'       => $m['og_image'] ?: $siteUrl . '/assets/images/og-default.jpg',
        'description' => $cfg['site_tagline'] ?? '',
        'telephone'   => $cfg['site_phone'] ?? '',
        'email'       => $cfg['site_email'] ?? '',
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $cfg['schema_address'] ?? '',
            'addressLocality' => $cfg['schema_city'] ?? 'Accra',
            'addressRegion'   => $cfg['schema_region'] ?? 'Greater Accra',
            'addressCountry'  => 'GH',
        ],
        'areaServed' => [
            ['@type'=>'Country','name'=>'Ghana'],
            ['@type'=>'City','name'=>'Accra'],
            ['@type'=>'City','name'=>'Kumasi'],
            ['@type'=>'City','name'=>'Takoradi'],
        ],
        'priceRange'  => $cfg['schema_price_range'] ?? '$$',
        'openingHoursSpecification' => [[
            '@type'      => 'OpeningHoursSpecification',
            'dayOfWeek'  => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
            'opens'      => $cfg['schema_opens'] ?? '08:00',
            'closes'     => $cfg['schema_closes'] ?? '18:00',
        ]],
        'currenciesAccepted' => 'GHS, USD',
        'paymentAccepted'    => 'Cash, Mobile Money, Bank Transfer',
    ];

    if (!empty($cfg['schema_lat']) && !empty($cfg['schema_lng'])) {
        $business['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float)$cfg['schema_lat'],
            'longitude' => (float)$cfg['schema_lng'],
        ];
    }
    if ($socialLinks) $business['sameAs'] = $socialLinks;

    $schemas[] = $business;

    // ── BreadcrumbList ────────────────────────────────────────
    $uri   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $parts = array_filter(explode('/', trim($uri, '/')));
    if (!empty($parts)) {
        $crumbs = [['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>$siteUrl]];
        $i = 2;
        foreach ($parts as $part) {
            $label = ucwords(str_replace(['-','.php'],['_',''],$part),'_');
            $label = str_replace('_',' ',$label);
            $crumbs[] = ['@type'=>'ListItem','position'=>$i++,'name'=>$label,'item'=>$siteUrl.'/'.$part];
        }
        if (count($crumbs) > 1) {
            $schemas[] = ['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$crumbs];
        }
    }

    // ── Article schema (blog posts) ───────────────────────────
    if (!empty($m['article'])) {
        $post = $m['article'];
        $schemas[] = [
            '@context'        => 'https://schema.org',
            '@type'           => 'Article',
            'headline'        => $post['title'],
            'description'     => $m['description'],
            'image'           => $m['og_image'] ?: '',
            'datePublished'   => date('c', strtotime($post['published_at'] ?? $post['created_at'])),
            'dateModified'    => date('c', strtotime($post['updated_at'] ?? $post['created_at'])),
            'author'          => ['@type'=>'Organization','name'=>$siteName,'url'=>$siteUrl],
            'publisher'       => [
                '@type' => 'Organization',
                'name'  => $siteName,
                'logo'  => ['@type'=>'ImageObject','url'=>$siteUrl.'/assets/images/tedmark logo copy2.png'],
            ],
            'mainEntityOfPage' => ['@type'=>'WebPage','@id'=>$m['canonical']],
            'inLanguage'      => 'en-GH',
        ];
    }

    // ── Output all schemas ─────────────────────────────────────
    echo "\n<!-- JSON-LD Structured Data -->\n";
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
    }
}

/**
 * Helper: generate an SEO score for a post (0-100).
 * Returns ['score'=>int, 'checks'=>[['label'=>'...','pass'=>bool,'tip'=>'...']]]
 */
function calcSeoScore(array $p): array {
    $checks = [];
    $score  = 0;

    $kw      = strtolower(trim($p['focus_keyword'] ?? ''));
    $title   = $p['seo_title'] ?: $p['title'];
    $desc    = $p['seo_description'] ?: ($p['excerpt'] ?? '');
    $body    = strip_tags($p['body'] ?? '');
    $titleLen = mb_strlen($title);
    $descLen  = mb_strlen($desc);
    $wordCount = str_word_count($body);

    // 1. SEO title set
    $hasSeoTitle = !empty($p['seo_title']);
    $checks[] = ['label'=>'Custom SEO title set','pass'=>$hasSeoTitle,'tip'=>'Set a custom SEO title separate from the post title.'];
    if ($hasSeoTitle) $score += 10;

    // 2. Title length 50–60
    $goodLen = $titleLen >= 50 && $titleLen <= 60;
    $checks[] = ['label'=>"Title length ({$titleLen} chars, ideal 50–60)",'pass'=>$goodLen,'tip'=>'Keep SEO title between 50–60 characters to avoid truncation in SERPs.'];
    if ($goodLen) $score += 15;
    elseif ($titleLen > 0) $score += 5;

    // 3. Meta description set
    $hasDesc = !empty($desc);
    $checks[] = ['label'=>'Meta description set','pass'=>$hasDesc,'tip'=>'Always write a compelling meta description.'];
    if ($hasDesc) $score += 10;

    // 4. Description length 120–160
    $goodDesc = $descLen >= 120 && $descLen <= 160;
    $checks[] = ['label'=>"Description length ({$descLen} chars, ideal 120–160)",'pass'=>$goodDesc,'tip'=>'Write a description between 120–160 characters for best SERP display.'];
    if ($goodDesc) $score += 10;
    elseif ($descLen > 0) $score += 3;

    // 5. Focus keyword set
    $hasKw = !empty($kw);
    $checks[] = ['label'=>'Focus keyword set','pass'=>$hasKw,'tip'=>'Set a target keyword to optimise this post around.'];
    if ($hasKw) $score += 5;

    if ($hasKw) {
        // 6. Keyword in title
        $kwInTitle = str_contains(strtolower($title), $kw);
        $checks[] = ['label'=>"Keyword in title",'pass'=>$kwInTitle,'tip'=>"Include \"{$kw}\" in the SEO title."];
        if ($kwInTitle) $score += 15;

        // 7. Keyword in description
        $kwInDesc = str_contains(strtolower($desc), $kw);
        $checks[] = ['label'=>"Keyword in meta description",'pass'=>$kwInDesc,'tip'=>"Include \"{$kw}\" in the meta description."];
        if ($kwInDesc) $score += 10;

        // 8. Keyword in body
        $kwInBody = str_contains(strtolower($body), $kw);
        $checks[] = ['label'=>"Keyword in post content",'pass'=>$kwInBody,'tip'=>"Use \"{$kw}\" naturally in the post body."];
        if ($kwInBody) $score += 5;

        // 9. Keyword density (1-3%)
        if ($wordCount > 0 && $kwInBody) {
            $kwWords = count(explode(' ', $kw));
            $kwCount = substr_count(strtolower($body), $kw);
            $density = round(($kwCount * $kwWords / $wordCount) * 100, 1);
            $goodDens = $density >= 0.5 && $density <= 3;
            $checks[] = ['label'=>"Keyword density ({$density}%, ideal 0.5–3%)",'pass'=>$goodDens,'tip'=>"Avoid keyword stuffing. Aim for natural usage."];
            if ($goodDens) $score += 5;
        }
    }

    // 10. Featured image set
    $hasImg = !empty($p['featured_image']) || !empty($p['og_image']);
    $checks[] = ['label'=>'Featured / OG image set','pass'=>$hasImg,'tip'=>'Images improve CTR in social shares. Set a featured image.'];
    if ($hasImg) $score += 10;

    // 11. Word count 300+
    $goodWords = $wordCount >= 300;
    $checks[] = ['label'=>"Word count ({$wordCount} words, aim 600+)",'pass'=>$wordCount >= 600,'tip'=>'Longer content (600+ words) tends to rank higher. Aim for depth.'];
    if ($wordCount >= 600) $score += 5;
    elseif ($goodWords)    $score += 2;

    return ['score' => min(100, $score), 'checks' => $checks];
}
