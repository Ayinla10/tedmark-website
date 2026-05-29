<?php
$pageTitle = 'SEO Manager';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$success = $error = '';

// Load settings
$rows = fetchAll("SELECT `key`, `value` FROM settings");
$s = array_column($rows, 'value', 'key');

// Load per-page SEO
$pageRows = fetchAll("SELECT * FROM seo_pages ORDER BY page_key ASC");
$pages = [];
foreach ($pageRows as $pr) $pages[$pr['page_key']] = $pr;

// ── Save handlers ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? 'global';

    if ($action === 'global' || $action === 'schema' || $action === 'social') {
        // Save settings
        $fields = $_POST['settings'] ?? [];
        foreach ($fields as $key => $val) {
            $key = preg_replace('/[^a-z0-9_]/', '', $key);
            $existing = fetchOne("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($existing) query("UPDATE settings SET `value` = ? WHERE `key` = ?", [trim($val), $key]);
            else           query("INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, 'seo')", [$key, trim($val)]);
        }

        // Write robots.txt if updated
        if ($action === 'global' && isset($fields['robots_txt_content'])) {
            $robotsContent = str_replace("\r\n", "\n", trim($fields['robots_txt_content']));
            file_put_contents(__DIR__ . '/../robots.txt', $robotsContent);
        }

        header('Location: ' . SITE_URL . '/admin/seo.php?saved=1&tab=' . $action); exit;
    }

    if ($action === 'pages') {
        // Save per-page SEO
        $pageData = $_POST['page'] ?? [];
        foreach ($pageData as $pageKey => $pd) {
            $pageKey = preg_replace('/[^a-z0-9_-]/', '', $pageKey);
            $exists  = fetchOne("SELECT id FROM seo_pages WHERE page_key = ?", [$pageKey]);
            $noIdx   = isset($pd['no_index']) ? 1 : 0;
            if ($exists) {
                query("UPDATE seo_pages SET meta_title=?,meta_description=?,meta_keywords=?,og_image=?,canonical_url=?,no_index=? WHERE page_key=?",
                    [trim($pd['meta_title']??''), trim($pd['meta_description']??''), trim($pd['meta_keywords']??''), trim($pd['og_image']??''), trim($pd['canonical_url']??''), $noIdx, $pageKey]);
            } else {
                query("INSERT INTO seo_pages (page_key,meta_title,meta_description,meta_keywords,og_image,canonical_url,no_index) VALUES (?,?,?,?,?,?,?)",
                    [$pageKey, trim($pd['meta_title']??''), trim($pd['meta_description']??''), trim($pd['meta_keywords']??''), trim($pd['og_image']??''), trim($pd['canonical_url']??''), $noIdx]);
            }
        }
        header('Location: ' . SITE_URL . '/admin/seo.php?saved=1&tab=pages'); exit;
    }

    if ($action === 'ping') {
        // Ping Google & Bing sitemaps
        $sitemapUrl = urlencode(SITE_URL . '/sitemap.xml.php');
        $results = [];
        $g = @file_get_contents("https://www.google.com/ping?sitemap=" . $sitemapUrl);
        $results[] = 'Google: ' . ($g !== false ? 'Pinged ✓' : 'Failed (try manually)');
        $b = @file_get_contents("https://www.bing.com/ping?sitemap=" . $sitemapUrl);
        $results[] = 'Bing: '   . ($b !== false ? 'Pinged ✓' : 'Failed (try manually)');
        $success = implode(' | ', $results);
    }
}

$savedTab = $_GET['tab'] ?? 'global';
if (isset($_GET['saved'])) $success = 'SEO settings saved successfully!';

require_once __DIR__ . '/includes/admin-layout.php';

// Page labels
$pageLabels = [
    'home'       => ['label'=>'Homepage',    'url'=>'/',             'icon'=>'fa-house'],
    'about'      => ['label'=>'About',       'url'=>'/about.php',    'icon'=>'fa-circle-info'],
    'services'   => ['label'=>'Services',    'url'=>'/services.php', 'icon'=>'fa-briefcase'],
    'portfolio'  => ['label'=>'Portfolio',   'url'=>'/portfolio.php','icon'=>'fa-folder-open'],
    'blog'       => ['label'=>'Blog',        'url'=>'/blog.php',     'icon'=>'fa-newspaper'],
    'contact'    => ['label'=>'Contact',     'url'=>'/contact.php',  'icon'=>'fa-envelope'],
    'industries' => ['label'=>'Industries',  'url'=>'/industries.php','icon'=>'fa-building'],
    'resources'  => ['label'=>'Resources',   'url'=>'/resources.php','icon'=>'fa-book'],
];

function sv($s, $k, $d='') { return htmlspecialchars($s[$k] ?? $d); }
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- Tab nav -->
<div style="display:flex;gap:4px;margin-bottom:24px;background:#0b1528;padding:4px;border-radius:10px;width:fit-content;flex-wrap:wrap;">
  <?php $tabs=[
    ['global','fa-gear','Global SEO'],
    ['pages','fa-file-lines','Pages'],
    ['schema','fa-code','Schema / Local'],
    ['robots','fa-robot','Robots & Sitemap'],
    ['social','fa-share-nodes','Social'],
  ]; ?>
  <?php foreach($tabs as [$tid,$tic,$tlabel]): ?>
  <button type="button" class="seo-tab-btn" data-tab="<?= $tid ?>" style="padding:7px 16px;border:none;border-radius:7px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all .15s;background:<?= $savedTab===$tid?'var(--card)':'transparent' ?>;color:<?= $savedTab===$tid?'#fff':'#64748b' ?>;">
    <i class="fa-solid <?= $tic ?>" style="margin-right:5px;"></i><?= $tlabel ?>
  </button>
  <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════
     GLOBAL SEO
══════════════════════════════════════════════ -->
<div class="seo-panel" id="seo-global" <?= $savedTab !== 'global' ? 'style="display:none"' : '' ?>>
<form method="POST">
<input type="hidden" name="_action" value="global">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-heading" style="color:#22c55e;margin-right:8px;"></i>Title & Description</span></div>
    <div class="form-group">
      <label>Title Template</label>
      <input type="text" name="settings[seo_title_template]" value="<?= sv($s,'seo_title_template','{title} | Tedmark Digital Agency') ?>">
      <small style="color:#64748b;font-size:0.75rem;">Use <code style="background:#0b1528;padding:1px 5px;border-radius:4px;">{title}</code> as placeholder. e.g. <em>{title} | Tedmark Digital Agency</em></small>
    </div>
    <div class="form-group">
      <label>Default Meta Description <span id="gd-len" style="color:#64748b;font-size:0.75rem;font-weight:400;">(<?= mb_strlen($s['seo_default_description']??'') ?>/160)</span></label>
      <textarea name="settings[seo_default_description]" id="gd-textarea" rows="3" oninput="document.getElementById('gd-len').textContent='('+this.value.length+'/160)'"><?= sv($s,'seo_default_description') ?></textarea>
      <small style="color:#64748b;font-size:0.75rem;">Used when a page has no custom description. Aim 150–160 chars.</small>
    </div>
    <div class="form-group">
      <label>Default Keywords</label>
      <textarea name="settings[seo_default_keywords]" rows="2"><?= sv($s,'seo_default_keywords') ?></textarea>
      <small style="color:#64748b;font-size:0.75rem;">Comma-separated. Include location terms: "web development Accra", "digital agency Ghana"</small>
    </div>
    <div class="form-group">
      <label>Default OG / Share Image URL</label>
      <input type="text" name="settings[seo_default_og_image]" value="<?= sv($s,'seo_default_og_image') ?>" placeholder="https://... (1200×630 recommended)">
      <small style="color:#64748b;font-size:0.75rem;">Shown when sharing any page on Facebook/LinkedIn/WhatsApp. 1200×630px.</small>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:20px;">
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-brands fa-google" style="color:#ea4335;margin-right:8px;"></i>Google Tools</span></div>
      <div class="form-group">
        <label>Google Analytics 4 Measurement ID</label>
        <input type="text" name="settings[seo_ga4_id]" value="<?= sv($s,'seo_ga4_id') ?>" placeholder="G-XXXXXXXXXX">
        <small style="color:#64748b;font-size:0.75rem;">Paste your GA4 Measurement ID to enable tracking on all pages.</small>
      </div>
      <div class="form-group">
        <label>Google Search Console Verification</label>
        <input type="text" name="settings[seo_gsc_verification]" value="<?= sv($s,'seo_gsc_verification') ?>" placeholder="abc123xyz...">
        <small style="color:#64748b;font-size:0.75rem;">Paste only the <em>content</em> value from the meta tag GSC gives you.</small>
      </div>
    </div>
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-brands fa-microsoft" style="color:#00a4ef;margin-right:8px;"></i>Bing Webmaster Tools</span></div>
      <div class="form-group">
        <label>Bing Verification Meta Content</label>
        <input type="text" name="settings[seo_bing_verification]" value="<?= sv($s,'seo_bing_verification') ?>" placeholder="abc123...">
        <small style="color:#64748b;font-size:0.75rem;">From Bing Webmaster Tools → Verify → Meta tag method → content value only.</small>
      </div>
    </div>
    <!-- Checklist -->
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-list-check" style="color:#f59e0b;margin-right:8px;"></i>Ghana Ranking Quick Checklist</span></div>
      <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:8px;">
        <?php
        $checks = [
            [!empty($s['seo_ga4_id']), 'Google Analytics 4 connected'],
            [!empty($s['seo_gsc_verification']), 'Google Search Console verified'],
            [!empty($s['seo_default_og_image']), 'Default OG image set'],
            [!empty($s['seo_default_keywords']) && str_contains(strtolower($s['seo_default_keywords']??''), 'ghana'), 'Keywords include "Ghana"'],
            [!empty($s['schema_address']), 'Business address set (local SEO)'],
            [!empty($s['schema_lat']), 'Business geo coordinates set'],
            [!empty($s['site_phone']), 'Phone number set in Settings'],
            [!empty($s['social_linkedin']) && $s['social_linkedin'] !== '#', 'LinkedIn profile linked'],
        ];
        foreach ($checks as [$pass, $label]):
        ?>
        <li style="display:flex;align-items:center;gap:10px;font-size:0.83rem;">
          <?php if ($pass): ?>
          <i class="fa-solid fa-circle-check" style="color:#22c55e;flex-shrink:0;"></i>
          <span style="color:#e2e8f0;"><?= $label ?></span>
          <?php else: ?>
          <i class="fa-solid fa-circle-xmark" style="color:#f43f5e;flex-shrink:0;"></i>
          <span style="color:#64748b;"><?= $label ?></span>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
<div style="margin-top:16px;"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Global SEO</button></div>
</form>
</div>

<!-- ══════════════════════════════════════════════
     PAGES
══════════════════════════════════════════════ -->
<div class="seo-panel" id="seo-pages" <?= $savedTab !== 'pages' ? 'style="display:none"' : '' ?>>
<form method="POST">
<input type="hidden" name="_action" value="pages">

<?php foreach ($pageLabels as $pk => $pl): $sp = $pages[$pk] ?? []; ?>
<div class="tm-card" style="margin-bottom:16px;">
  <div class="tm-card-header">
    <span class="tm-card-title">
      <i class="fa-solid <?= $pl['icon'] ?>" style="color:#22c55e;margin-right:8px;"></i>
      <?= $pl['label'] ?>
    </span>
    <div style="display:flex;align-items:center;gap:10px;">
      <a href="<?= SITE_URL . $pl['url'] ?>" target="_blank" style="color:#64748b;font-size:0.78rem;text-decoration:none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View</a>
      <label style="display:flex;align-items:center;gap:6px;font-size:0.78rem;color:#94a3b8;text-transform:none;cursor:pointer;">
        <input type="checkbox" name="page[<?= $pk ?>][no_index]" value="1" <?= !empty($sp['no_index'])?'checked':'' ?> style="width:auto;accent-color:#f43f5e;">
        No-index (hide from Google)
      </label>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div>
      <div class="form-group">
        <label>SEO Title <span id="pt-len-<?= $pk ?>" style="color:#64748b;font-weight:400;font-size:0.75rem;">(<?= mb_strlen($sp['meta_title']??'') ?>/60)</span></label>
        <input type="text" name="page[<?= $pk ?>][meta_title]" value="<?= htmlspecialchars($sp['meta_title']??'') ?>"
          placeholder="e.g. Web Development Agency in Accra, Ghana | Tedmark"
          oninput="document.getElementById('pt-len-<?= $pk ?>').textContent='('+this.value.length+'/60)'; updateSerp('<?= $pk ?>', this.value, null)">
      </div>
      <div class="form-group">
        <label>Meta Description <span id="pd-len-<?= $pk ?>" style="color:#64748b;font-weight:400;font-size:0.75rem;">(<?= mb_strlen($sp['meta_description']??'') ?>/160)</span></label>
        <textarea name="page[<?= $pk ?>][meta_description]" rows="2"
          placeholder="150–160 chars. Include location: Accra, Ghana."
          oninput="document.getElementById('pd-len-<?= $pk ?>').textContent='('+this.value.length+'/160)'; updateSerp('<?= $pk ?>',null,this.value)"><?= htmlspecialchars($sp['meta_description']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label>Focus Keywords</label>
        <input type="text" name="page[<?= $pk ?>][meta_keywords]" value="<?= htmlspecialchars($sp['meta_keywords']??'') ?>" placeholder="web development Ghana, digital agency Accra">
      </div>
    </div>
    <div>
      <div class="form-group">
        <label>OG / Share Image URL</label>
        <input type="text" name="page[<?= $pk ?>][og_image]" value="<?= htmlspecialchars($sp['og_image']??'') ?>" placeholder="https://... (1200×630)">
      </div>
      <div class="form-group">
        <label>Canonical URL <span style="color:#64748b;font-size:0.73rem;font-weight:400;">(leave blank = auto)</span></label>
        <input type="text" name="page[<?= $pk ?>][canonical_url]" value="<?= htmlspecialchars($sp['canonical_url']??'') ?>" placeholder="https://new.tedmarkdigital.com/...">
      </div>
      <!-- SERP Preview -->
      <div style="background:#0b1528;border:1px solid #1e293b;border-radius:8px;padding:12px;">
        <p style="color:#64748b;font-size:0.7rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;margin-bottom:8px;">Google Preview</p>
        <div id="serp-url-<?= $pk ?>" style="color:#4ade80;font-size:0.78rem;margin-bottom:3px;"><?= htmlspecialchars(str_replace('https://','',SITE_URL) . $pl['url']) ?></div>
        <div id="serp-title-<?= $pk ?>" style="color:#93c5fd;font-size:0.9rem;font-weight:600;line-height:1.3;margin-bottom:4px;"><?= htmlspecialchars($sp['meta_title'] ?: $pl['label'] . ' | Tedmark Digital Agency') ?></div>
        <div id="serp-desc-<?= $pk ?>" style="color:#94a3b8;font-size:0.78rem;line-height:1.5;"><?= htmlspecialchars(substr($sp['meta_description'] ?? 'Add a meta description to show here...', 0, 155)) ?></div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div style="margin-top:8px;"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save All Pages SEO</button></div>
</form>
</div>

<!-- ══════════════════════════════════════════════
     SCHEMA / LOCAL BUSINESS
══════════════════════════════════════════════ -->
<div class="seo-panel" id="seo-schema" <?= $savedTab !== 'schema' ? 'style="display:none"' : '' ?>>
<form method="POST">
<input type="hidden" name="_action" value="schema">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-location-dot" style="color:#f43f5e;margin-right:8px;"></i>Business Information</span></div>
    <p style="color:#64748b;font-size:0.82rem;margin-bottom:16px;">This data appears in Google's Knowledge Panel and local search results.</p>
    <div class="form-group"><label>Street Address</label><input type="text" name="settings[schema_address]" value="<?= sv($s,'schema_address') ?>" placeholder="e.g. 12 Osu Oxford Street"></div>
    <div class="form-row">
      <div class="form-group"><label>City</label><input type="text" name="settings[schema_city]" value="<?= sv($s,'schema_city','Accra') ?>"></div>
      <div class="form-group"><label>Region</label><input type="text" name="settings[schema_region]" value="<?= sv($s,'schema_region','Greater Accra') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Opens (24h)</label><input type="time" name="settings[schema_opens]" value="<?= sv($s,'schema_opens','08:00') ?>"></div>
      <div class="form-group"><label>Closes (24h)</label><input type="time" name="settings[schema_closes]" value="<?= sv($s,'schema_closes','18:00') ?>"></div>
    </div>
    <div class="form-group">
      <label>Price Range</label>
      <select name="settings[schema_price_range]">
        <?php foreach(['$'=>'$ (Budget)','$$'=>'$$ (Moderate)','$$$'=>'$$$ (Premium)','$$$$'=>'$$$$ (Enterprise)'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= sv($s,'schema_price_range','$$')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-map-pin" style="color:#22c55e;margin-right:8px;"></i>GPS Coordinates</span></div>
    <p style="color:#64748b;font-size:0.82rem;margin-bottom:16px;">Helps Google Maps surface your business. Find coords at <a href="https://maps.google.com" target="_blank" style="color:#22c55e;">maps.google.com</a> → right-click your location.</p>
    <div class="form-row">
      <div class="form-group"><label>Latitude</label><input type="text" name="settings[schema_lat]" value="<?= sv($s,'schema_lat','5.6037') ?>" placeholder="5.6037"></div>
      <div class="form-group"><label>Longitude</label><input type="text" name="settings[schema_lng]" value="<?= sv($s,'schema_lng','-0.1870') ?>" placeholder="-0.1870"></div>
    </div>
    <div style="background:#0b1528;border:1px solid #1e293b;border-radius:8px;padding:14px;margin-top:4px;">
      <p style="color:#64748b;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Why This Matters for Ghana Rankings</p>
      <ul style="color:#94a3b8;font-size:0.78rem;line-height:1.9;padding-left:16px;">
        <li>Powers <strong style="color:#e2e8f0;">LocalBusiness JSON-LD</strong> on every page</li>
        <li>Signals to Google your business is in <strong style="color:#e2e8f0;">Accra, Ghana</strong></li>
        <li>Appears in <strong style="color:#e2e8f0;">"near me"</strong> searches in Ghana</li>
        <li>Geo tags set <strong style="color:#e2e8f0;">GH-AA</strong> (Greater Accra) region</li>
        <li>All pages get <strong style="color:#e2e8f0;">hreflang="en-GH"</strong> signals</li>
      </ul>
    </div>
  </div>
</div>
<div style="margin-top:16px;"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Schema Data</button></div>
</form>
</div>

<!-- ══════════════════════════════════════════════
     ROBOTS & SITEMAP
══════════════════════════════════════════════ -->
<div class="seo-panel" id="seo-robots" <?= $savedTab !== 'robots' ? 'style="display:none"' : '' ?>>
<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

  <form method="POST">
  <input type="hidden" name="_action" value="global">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-robot" style="color:#22c55e;margin-right:8px;"></i>robots.txt Editor</span></div>
    <p style="color:#64748b;font-size:0.82rem;margin-bottom:14px;">Controls what pages search engines can crawl. Saving this will write to your <code style="background:#0b1528;padding:2px 6px;border-radius:4px;font-size:0.75rem;">robots.txt</code> file.</p>
    <div class="form-group">
      <textarea name="settings[robots_txt_content]" rows="14" style="font-family:monospace;font-size:0.82rem;"><?= htmlspecialchars($s['robots_txt_content'] ?? "User-agent: *\r\nAllow: /\r\nDisallow: /admin/\r\nDisallow: /includes/\r\nDisallow: /database/\r\n\r\nSitemap: " . SITE_URL . "/sitemap.xml.php") ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save robots.txt</button>
    <a href="<?= SITE_URL ?>/robots.txt" target="_blank" class="btn btn-ghost btn-sm" style="margin-left:8px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View robots.txt</a>
  </div>
  </form>

  <div style="display:flex;flex-direction:column;gap:16px;">
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-sitemap" style="color:#f59e0b;margin-right:8px;"></i>XML Sitemap</span></div>
      <p style="color:#64748b;font-size:0.82rem;margin-bottom:14px;">Your sitemap is auto-generated and always up-to-date. Submit it to search consoles.</p>
      <a href="<?= SITE_URL ?>/sitemap.xml.php" target="_blank" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Sitemap</a>
      <form method="POST">
        <input type="hidden" name="_action" value="ping">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-satellite-dish"></i> Ping Google & Bing</button>
      </form>
      <div style="margin-top:14px;background:#0b1528;border-radius:8px;padding:12px;border:1px solid #1e293b;">
        <p style="color:#64748b;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Manual Submission URLs</p>
        <div style="display:flex;flex-direction:column;gap:6px;">
          <a href="https://search.google.com/search-console" target="_blank" style="color:#22c55e;font-size:0.8rem;text-decoration:none;"><i class="fa-brands fa-google fa-sm" style="margin-right:5px;"></i>Google Search Console</a>
          <a href="https://www.bing.com/webmasters" target="_blank" style="color:#60a5fa;font-size:0.8rem;text-decoration:none;"><i class="fa-brands fa-microsoft fa-sm" style="margin-right:5px;color:#00a4ef;"></i>Bing Webmaster Tools</a>
        </div>
      </div>
    </div>

    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-trophy" style="color:#f59e0b;margin-right:8px;"></i>Rank #1 in Ghana — Action Plan</span></div>
      <ol style="padding-left:16px;color:#94a3b8;font-size:0.78rem;line-height:2;">
        <li>✅ Submit sitemap to Google Search Console</li>
        <li>✅ Verify site with GSC meta tag</li>
        <li>✅ Set up Google Analytics 4</li>
        <li>📍 Create Google Business Profile (maps.google.com)</li>
        <li>📝 Publish 2–3 blog posts/month with Ghana keywords</li>
        <li>🔗 Get backlinks from .gh sites, directories</li>
        <li>⚡ Score 90+ on PageSpeed Insights</li>
        <li>🏷️ Fill SEO for every page above</li>
        <li>📱 Ensure site is mobile-friendly</li>
        <li>🧱 Build citations: Ghana Business Directory, etc.</li>
      </ol>
    </div>
  </div>
</div>
</div>

<!-- ══════════════════════════════════════════════
     SOCIAL
══════════════════════════════════════════════ -->
<div class="seo-panel" id="seo-social" <?= $savedTab !== 'social' ? 'style="display:none"' : '' ?>>
<form method="POST">
<input type="hidden" name="_action" value="social">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-share-nodes" style="color:#22c55e;margin-right:8px;"></i>Social Sharing</span></div>
    <div class="form-group">
      <label>Twitter / X Handle</label>
      <input type="text" name="settings[seo_twitter_handle]" value="<?= sv($s,'seo_twitter_handle') ?>" placeholder="@TedmarkDigital">
      <small style="color:#64748b;font-size:0.75rem;">Used for twitter:site card attribution.</small>
    </div>
    <div class="form-group">
      <label>Default OG Image (Social Share)</label>
      <input type="text" name="settings[seo_default_og_image]" value="<?= sv($s,'seo_default_og_image') ?>" placeholder="https://... (1200×630px)">
      <small style="color:#64748b;font-size:0.75rem;">Shown when anyone shares your site on WhatsApp, Facebook, LinkedIn, Twitter.</small>
    </div>
    <?php if (!empty($s['seo_default_og_image'])): ?>
    <div style="border-radius:8px;overflow:hidden;margin-top:4px;">
      <img src="<?= htmlspecialchars($s['seo_default_og_image']) ?>" style="width:100%;height:120px;object-fit:cover;display:block;" onerror="this.style.display='none'">
      <p style="background:#1e293b;color:#64748b;font-size:0.7rem;padding:5px 10px;text-align:center;">OG image preview (1200×630)</p>
    </div>
    <?php endif; ?>
  </div>
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-circle-info" style="color:#60a5fa;margin-right:8px;"></i>OG Image Best Practices</span></div>
    <ul style="color:#94a3b8;font-size:0.82rem;line-height:2;padding-left:16px;">
      <li>Size: <strong style="color:#e2e8f0;">1200 × 630 pixels</strong></li>
      <li>Format: JPG or PNG (under 1MB)</li>
      <li>Include your <strong style="color:#e2e8f0;">logo + tagline</strong></li>
      <li>Use a <strong style="color:#e2e8f0;">dark background</strong> for brand consistency</li>
      <li>Each page/post can override this with its own image</li>
      <li>Test at <a href="https://developers.facebook.com/tools/debug/" target="_blank" style="color:#22c55e;">Facebook Debugger</a></li>
      <li>Test at <a href="https://cards-dev.twitter.com/validator" target="_blank" style="color:#22c55e;">Twitter Card Validator</a></li>
    </ul>
  </div>
</div>
<div style="margin-top:16px;"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Social SEO</button></div>
</form>
</div>

<script>
// Tab switching
const tabs = document.querySelectorAll('.seo-tab-btn');
const panels = document.querySelectorAll('.seo-panel');
tabs.forEach(btn => {
  btn.addEventListener('click', () => {
    panels.forEach(p => p.style.display = 'none');
    tabs.forEach(b => { b.style.background='transparent'; b.style.color='#64748b'; });
    document.getElementById('seo-' + btn.dataset.tab).style.display = '';
    btn.style.background = 'var(--card)';
    btn.style.color = '#fff';
  });
});

// Live SERP preview updater
function updateSerp(pk, title, desc) {
  if (title !== null) {
    const el = document.getElementById('serp-title-' + pk);
    if (el) el.textContent = title || '...';
  }
  if (desc !== null) {
    const el = document.getElementById('serp-desc-' + pk);
    if (el) el.textContent = (desc || 'Add a meta description...').substring(0, 155);
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
