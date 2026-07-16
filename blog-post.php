<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . SITE_URL . '/blog'); exit; }

// ── Fallback post (CMS will replace via DB) ───────────────────────────────
$fallbackPosts = [
    'how-automation-saves-african-businesses-time' => [
        'slug'         => 'how-automation-saves-african-businesses-time',
        'title'        => 'How Automation Is Saving Businesses 10+ Hours Every Week',
        'excerpt'      => 'Manual processes are quietly draining your time, your team, and your profits. Here\'s how smart automation changes everything.',
        'content'      => '<p>Every week, thousands of business owners spend hours on tasks that could be handled automatically. Invoicing. Sending reminders. Updating spreadsheets. Following up on leads. These tasks feel necessary, and they are, but they don\'t have to be done by a human.</p><p>Business automation is no longer a luxury for large corporations. Today, even a 5-person team can implement systems that eliminate repetitive work and redirect human energy where it actually matters: building relationships, making decisions, and growing the business.</p><h2>What kinds of tasks can be automated?</h2><p>The most common time-wasters we see in small and mid-sized businesses include invoice generation and follow-up, customer onboarding emails, appointment reminders, stock level alerts, report generation, and social media scheduling. Each of these might only take 15–30 minutes per day, but multiply that across a team of five people, and you\'re looking at 10–20 hours per week of recoverable time.</p><h2>The real cost of doing it manually</h2><p>It\'s not just about time. Manual processes introduce human error. An invoice sent to the wrong client. A follow-up forgotten. A stock order placed too late. These mistakes have real financial consequences: lost clients, damaged reputation, and avoidable costs.</p><h2>Where to start</h2><p>Start with your biggest time drain. For most businesses, that\'s client communication or financial admin. Map out the process, every step, and identify which steps require a human decision and which are just execution. The execution steps are your automation opportunities.</p><p>You don\'t need to automate everything at once. One well-built automation can save an hour a day. That\'s 260 hours a year: more than 6 full work weeks returned to your business.</p>',
        'cat_name'     => 'Automation',
        'author_name'  => 'Tedmark Team',
        'published_at' => '2024-11-15',
        'read_time'    => '5',
        'views'        => 1240,
        'has_audio'    => false,
        'audio_url'    => '',
        'featured_image' => '',
        'seo_keywords' => 'automation,business systems,SME,productivity',
        'tags'         => ['Automation', 'Productivity', 'SME'],
    ],
    'why-your-website-is-losing-you-customers' => [
        'slug'         => 'why-your-website-is-losing-you-customers',
        'title'        => 'Why Your Website Is Quietly Losing You Customers Every Day',
        'excerpt'      => 'A slow, outdated, or hard-to-navigate website doesn\'t just frustrate visitors, it actively costs you money. Here\'s what to look for.',
        'content'      => '<p>Your website is often the first impression a potential customer has of your business. Before they call you. Before they visit. Before they trust you. And if that first impression is slow to load, hard to navigate, or looks outdated, they leave. Quietly. Without telling you.</p><p>This happens thousands of times a day across businesses of every size. Potential clients arrive, see something that doesn\'t inspire confidence, and move on to a competitor. You never know they were there.</p><h2>The 3-second rule</h2><p>Research consistently shows that users will abandon a website that takes more than 3 seconds to load. On mobile, where most of your audience is browsing, slow pages are even more punishing. If your site isn\'t loading fast, you\'re losing visitors before they even see your offering.</p><h2>Mobile is everything</h2><p>Over 60% of global internet traffic comes from mobile devices. If your website wasn\'t designed with mobile in mind (small text, elements that don\'t fit the screen, buttons that are too small to tap), then you\'re delivering a broken experience to most of your audience.</p><h2>Trust signals matter</h2><p>A professional website communicates credibility. An outdated one communicates the opposite. People make split-second judgements about whether to trust a business based on how its website looks. Your website should feel as professional as your best salesperson.</p><h2>What to fix first</h2><p>Speed. Mobile responsiveness. Clear navigation. A strong, visible call to action. These four things, done well, can transform your conversion rate. You don\'t need a completely new website overnight, but you do need a plan.</p>',
        'cat_name'     => 'Web Development',
        'author_name'  => 'Tedmark Team',
        'published_at' => '2024-10-22',
        'read_time'    => '4',
        'views'        => 890,
        'has_audio'    => false,
        'audio_url'    => '',
        'featured_image' => '',
        'seo_keywords' => 'website,mobile,web design,conversion',
        'tags'         => ['Web Design', 'Mobile', 'Conversion'],
    ],
];

// ── Resolve post ──────────────────────────────────────────────────────────
$post       = null;
$isFallback = false;

try {
    $post = fetchOne("SELECT p.*, u.name as author_name
                      FROM posts p
                      LEFT JOIN users u ON p.author_id = u.id
                      WHERE p.slug = ? AND p.status = 'published'", [$slug]);
    if ($post) {
        query("UPDATE posts SET views = views + 1 WHERE id = ?", [$post['id']]);
        $related = fetchAll("SELECT * FROM posts
                             WHERE status = 'published' AND id != ? AND category = ?
                             ORDER BY published_at DESC LIMIT 2",
                            [$post['id'], $post['category']]);
    }
} catch (Exception $e) { $post = null; }

if (!$post) {
    $post       = $fallbackPosts[$slug] ?? reset($fallbackPosts);
    $isFallback = true;
    $related    = array_values(array_filter($fallbackPosts, fn($p) => $p['slug'] !== $post['slug']));
}

if (!$post) { header('Location: ' . SITE_URL . '/blog'); exit; }

// ── Derived values ────────────────────────────────────────────────────────
$title      = htmlspecialchars($post['title']);
$excerpt    = htmlspecialchars($post['excerpt'] ?? '');
$author     = htmlspecialchars($post['author_name'] ?? 'Tedmark Team');
$category   = htmlspecialchars($isFallback ? ($post['cat_name'] ?? 'Article') : ($post['category'] ?? 'Article'));
$pubDate    = $post['published_at'] ?? $post['created_at'] ?? date('Y-m-d');
$body       = $isFallback ? ($post['content'] ?? '') : ($post['body'] ?? '');
$readTime   = $isFallback ? $post['read_time'] : max(1, (int)($post['read_time'] ?? ceil(str_word_count(strip_tags($body)) / 200)));
$views      = number_format($post['views'] ?? 0);
$hasAudio   = !empty($post['audio_url']) || !empty($post['has_audio']);
$audioUrl   = $post['audio_url'] ?? '';
$tags       = array_filter(array_map('trim', explode(',', $post['tags'] ?? '')));
$postUrl    = SITE_URL . '/blog-post?slug=' . urlencode($post['slug'] ?? $slug);

$pageTitle       = $post['title'] . ' | Blog';
$pageDesc        = strip_tags($post['excerpt'] ?? '');
$pageHasDarkHero = false;
$seoData         = ['post' => $post];

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Reading typography ─────────────────────────────── */
.tm-post-body {
    font-family: 'Inter', sans-serif;
    font-size: 1.1rem;
    line-height: 1.9;
    color: #374151;
}
.tm-post-body p { margin-bottom: 1.6rem; }
.tm-post-body h2 {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    margin: 2.8rem 0 1rem;
    line-height: 1.3;
}
.tm-post-body h3 {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.2rem;
    font-weight:600;
    color: #0f172a;
    margin: 2rem 0 .8rem;
}
.tm-post-body ul, .tm-post-body ol {
    padding-left: 1.5rem;
    margin-bottom: 1.6rem;
}
.tm-post-body li { margin-bottom: .5rem; }
.tm-post-body blockquote {
    border-left: 4px solid #16a34a;
    padding: 16px 24px;
    background: #f0fdf4;
    border-radius: 0 12px 12px 0;
    margin: 2rem 0;
    font-style: italic;
    color: #166534;
}
.tm-post-body a { color: #16a34a; text-decoration: underline; }
.tm-post-body strong { color: #0f172a; font-weight:600; }

/* ── Audio player ───────────────────────────────────── */
.tm-audio-player {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border-radius: 18px;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 48px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}
.tm-audio-play-btn {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    border: none; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1rem;
    box-shadow: 0 4px 16px rgba(22,163,74,0.4);
    transition: transform .2s, box-shadow .2s;
}
.tm-audio-play-btn:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(22,163,74,0.5); }
.tm-audio-progress {
    flex: 1;
    -webkit-appearance: none;
    height: 4px;
    border-radius: 99px;
    background: rgba(255,255,255,0.15);
    outline: none;
    cursor: pointer;
}
.tm-audio-progress::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: #22c55e;
    cursor: pointer;
}

/* ── Progress bar (reading) ─────────────────────────── */
#reading-progress {
    position: fixed; top: 0; left: 0; height: 3px;
    background: linear-gradient(90deg, #16a34a, #22c55e);
    z-index: 9999; width: 0%; transition: width .1s;
}

@media(max-width:768px) {
    .tm-blog-layout { grid-template-columns: 1fr !important; }
    .tm-blog-sidebar { display: none; }
}
</style>

<!-- Reading progress bar -->
<div id="reading-progress"></div>

<!-- ===== HERO ===== -->
<div style="background:#fff;padding:120px 0 0;border-bottom:1px solid #f1f5f9;">
    <div class="tm-container">
        <div style="max-width:760px;margin:0 auto;">

            <!-- Breadcrumb -->
            <div style="display:flex;align-items:center;gap:8px;font-size:0.78rem;color:#94a3b8;margin-bottom:32px;">
                <a href="<?= SITE_URL ?>/" style="color:#94a3b8;text-decoration:none;">Home</a>
                <i class="fa-solid fa-chevron-right fa-2xs"></i>
                <a href="<?= SITE_URL ?>/blog" style="color:#94a3b8;text-decoration:none;">Blog</a>
                <i class="fa-solid fa-chevron-right fa-2xs"></i>
                <span style="color:#64748b;"><?= $category ?></span>
            </div>

            <!-- Category -->
            <div style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;color:#16a34a;font-size:0.72rem;font-weight:600;padding:5px 14px;border-radius:99px;margin-bottom:24px;text-transform:uppercase;letter-spacing:.08em;">
                <i class="fa-solid fa-tag fa-xs"></i> <?= $category ?>
            </div>

            <!-- Title -->
            <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.9rem,4vw,2.9rem);font-weight:900;color:#0f172a;line-height:1.15;margin:0 0 24px;letter-spacing:-.02em;">
                <?= $title ?>
            </h1>

            <!-- Excerpt -->
            <p style="font-size:1.15rem;color:#64748b;line-height:1.75;margin:0 0 36px;max-width:680px;">
                <?= $excerpt ?>
            </p>

            <!-- Meta row -->
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding-bottom:32px;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:600;color:#fff;flex-shrink:0;">
                    <?= strtoupper(substr($author, 0, 1)) ?>
                </div>
                <span style="font-size:0.875rem;font-weight:500;color:#0f172a;margin-left:4px;"><?= $author ?></span>
                <span style="color:#e2e8f0;margin:0 4px;">·</span>
                <span style="font-size:0.875rem;color:#94a3b8;"><?= date('F j, Y', strtotime($pubDate)) ?></span>
                <span style="color:#e2e8f0;margin:0 4px;">·</span>
                <span style="font-size:0.875rem;color:#94a3b8;display:flex;align-items:center;gap:4px;"><i class="fa-regular fa-clock fa-xs"></i> <?= $readTime ?> min read</span>
                <?php if($views): ?>
                <span style="color:#e2e8f0;margin:0 4px;">·</span>
                <span style="font-size:0.875rem;color:#94a3b8;display:flex;align-items:center;gap:4px;"><i class="fa-regular fa-eye fa-xs"></i> <?= $views ?></span>
                <?php endif; ?>
                <?php if($hasAudio): ?>
                <span style="color:#e2e8f0;margin:0 4px;">·</span>
                <span style="font-size:0.78rem;font-weight:600;background:#0f172a;color:#4ade80;padding:4px 10px;border-radius:99px;display:flex;align-items:center;gap:5px;">
                    <i class="fa-solid fa-headphones fa-xs"></i> Audio Available
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== FEATURED IMAGE ===== -->
<?php
$imgUrl = '';
if (!empty($post['featured_image'])) {
    $imgUrl = $post['featured_image'];
} elseif (!empty($post['og_image'])) {
    $imgUrl = $post['og_image'];
} else {
    $catImages = [
        'Automation'       => 'photo-1518770660439-4636190af475',
        'Web Development'  => 'photo-1555066931-4365d14bab8c',
        'Business Systems' => 'photo-1460925895917-afdab827c52f',
        'Digital Marketing'=> 'photo-1611974789855-9c2a0a7236a3',
    ];
    $imgId  = $catImages[$post['category'] ?? $post['cat_name'] ?? ''] ?? 'photo-1504868584819-f8e8b4b6d7e3';
    $imgUrl = "https://images.unsplash.com/{$imgId}?w=1200&q=80&auto=format&fit=crop";
}
?>
<div style="background:#f8fafc;padding:0 0 0;">
    <div class="tm-container">
        <div style="max-width:900px;margin:0 auto;">
            <img src="<?= $imgUrl ?>" alt="<?= $title ?>"
                 style="width:100%;height:420px;object-fit:cover;border-radius:0 0 24px 24px;display:block;box-shadow:0 16px 48px rgba(0,0,0,0.12);">
        </div>
    </div>
</div>

<!-- ===== CONTENT ===== -->
<section style="padding:72px 0 96px;background:#fff;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1fr 280px;gap:64px;max-width:1100px;margin:0 auto;align-items:start;" class="tm-blog-layout">

            <!-- Main content -->
            <div>

                <!-- Audio player -->
                <?php if($hasAudio && $audioUrl): ?>
                <div class="tm-audio-player" id="audio-wrap">
                    <button class="tm-audio-play-btn" id="audio-play" onclick="toggleAudio()">
                        <i class="fa-solid fa-play" id="audio-icon"></i>
                    </button>
                    <div style="flex:1;">
                        <div style="font-size:0.75rem;font-weight:600;color:#4ade80;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
                            <i class="fa-solid fa-headphones fa-xs"></i> Listen to this article
                        </div>
                        <input type="range" class="tm-audio-progress" id="audio-progress" value="0" min="0" max="100">
                        <div style="display:flex;justify-content:space-between;margin-top:6px;">
                            <span style="font-size:0.7rem;color:#64748b;" id="audio-current">0:00</span>
                            <span style="font-size:0.7rem;color:#64748b;" id="audio-duration">--:--</span>
                        </div>
                    </div>
                    <audio id="audio-el" src="<?= htmlspecialchars($audioUrl) ?>" preload="metadata"></audio>
                </div>
                <?php endif; ?>

                <!-- Article body -->
                <div class="tm-post-body" id="post-body">
                    <?= $body ?>
                </div>

                <!-- Tags -->
                <?php if(!empty($tags)): ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:48px;padding-top:32px;border-top:1px solid #f1f5f9;align-items:center;">
                    <span style="font-size:0.78rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Tags:</span>
                    <?php foreach($tags as $tag): if(!trim($tag)) continue; ?>
                    <span style="font-size:0.78rem;font-weight:500;background:#f8fafc;color:#475569;padding:5px 12px;border-radius:8px;border:1px solid #e2e8f0;"><?= htmlspecialchars(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Share -->
                <div style="display:flex;align-items:center;gap:12px;margin-top:28px;flex-wrap:wrap;">
                    <span style="font-size:0.82rem;font-weight:600;color:#64748b;">Share this article:</span>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($title) ?>&url=<?= urlencode($postUrl) ?>" target="_blank"
                       style="width:36px;height:36px;background:#0f172a;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.85rem;transition:opacity .2s;" title="Share on X">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($postUrl) ?>" target="_blank"
                       style="width:36px;height:36px;background:#0a66c2;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.85rem;" title="Share on LinkedIn">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($title . ' ' . $postUrl) ?>" target="_blank"
                       style="width:36px;height:36px;background:#25d366;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.85rem;" title="Share on WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <button onclick="navigator.clipboard.writeText('<?= $postUrl ?>');this.innerHTML='<i class=\'fa-solid fa-check\'></i> Copied!';setTimeout(()=>this.innerHTML='<i class=\'fa-solid fa-link\'></i> Copy link',2000)"
                       style="display:flex;align-items:center;gap:6px;height:36px;padding:0 14px;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;border-radius:99px;font-size:0.78rem;font-weight:500;cursor:pointer;">
                        <i class="fa-solid fa-link"></i> Copy link
                    </button>
                </div>

                <!-- Author card -->
                <div style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:20px;padding:28px 32px;margin-top:48px;display:flex;align-items:flex-start;gap:20px;">
                    <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;color:#fff;flex-shrink:0;">
                        <?= strtoupper(substr($author, 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Written by</div>
                        <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:6px;"><?= $author ?></div>
                        <p style="font-size:0.875rem;color:#64748b;line-height:1.65;margin:0;">
                            The Tedmark team writes about business technology, automation, and digital transformation for businesses ready to grow smarter.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <aside class="tm-blog-sidebar" style="position:sticky;top:120px;display:flex;flex-direction:column;gap:24px;">

                <!-- Table of contents -->
                <div style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:18px;padding:24px;" id="toc-box">
                    <div style="font-size:0.75rem;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">In this article</div>
                    <div id="toc-list" style="display:flex;flex-direction:column;gap:8px;"></div>
                </div>

                <!-- Newsletter -->
                <div style="background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:18px;padding:24px;">
                    <i class="fa-solid fa-envelope-open-text" style="color:#4ade80;font-size:1.3rem;margin-bottom:12px;display:block;"></i>
                    <h3 style="font-size:0.95rem;font-weight:800;color:#fff;margin:0 0 8px;">Get articles like this</h3>
                    <p style="font-size:0.8rem;color:#94a3b8;margin:0 0 16px;line-height:1.6;">Weekly insights on business technology, automation, and growth for growing businesses.</p>
                    <input type="email" placeholder="Your email address"
                           style="width:100%;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.07);color:#fff;font-size:0.85rem;margin-bottom:10px;box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    <button style="width:100%;padding:10px;background:#16a34a;color:#fff;border:none;border-radius:10px;font-weight:600;font-size:0.875rem;cursor:pointer;">
                        Subscribe Free
                    </button>
                </div>

                <!-- CTA -->
                <div style="background:#fff;border:1px solid #f1f5f9;border-radius:18px;padding:24px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.04);">
                    <i class="fa-solid fa-rocket" style="font-size:1.5rem;color:#16a34a;margin-bottom:12px;display:block;"></i>
                    <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin:0 0 8px;">Ready to implement this?</h3>
                    <p style="font-size:0.8rem;color:#64748b;margin:0 0 16px;line-height:1.6;">Book a free strategy session and we'll show you exactly how.</p>
                    <a href="<?= SITE_URL ?>/consultation" style="display:block;background:#16a34a;color:#fff;padding:11px;border-radius:10px;font-weight:600;font-size:0.875rem;text-decoration:none;">
                        Book Free Session <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                </div>

            </aside>
        </div>
    </div>
</section>

<!-- ===== RELATED POSTS ===== -->
<?php if(!empty($related)): ?>
<section style="padding:72px 0;background:#f8fafc;border-top:1px solid #f1f5f9;">
    <div class="tm-container">
        <div style="max-width:1100px;margin:0 auto;">
            <h2 style="font-size:1.4rem;font-weight:900;color:#0f172a;margin:0 0 36px;">Keep Reading</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;">
                <?php foreach(array_slice($related, 0, 2) as $r):
                    $rSlug    = $r['slug'] ?? '';
                    $rTitle   = htmlspecialchars($r['title']);
                    $rCat     = htmlspecialchars($r['category'] ?? ($r['cat_name'] ?? 'Article'));
                    $rExcerpt = htmlspecialchars(substr(strip_tags($r['excerpt'] ?? ''), 0, 110));
                ?>
                <a href="<?= SITE_URL ?>/blog-post?slug=<?= urlencode($rSlug) ?>"
                   style="background:#fff;border:1px solid #f1f5f9;border-radius:20px;overflow:hidden;text-decoration:none;display:block;transition:transform .25s,box-shadow .25s;"
                   onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(0,0,0,0.08)'"
                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div style="height:180px;background:linear-gradient(135deg,#0f172a,#1e3a5f);display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-newspaper" style="font-size:2.5rem;color:#22c55e;opacity:0.4;"></i>
                    </div>
                    <div style="padding:22px 24px;">
                        <div style="font-size:0.7rem;font-weight:600;color:#16a34a;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;"><?= $rCat ?></div>
                        <h3 style="font-size:1rem;font-weight:800;color:#0f172a;line-height:1.4;margin:0 0 10px;"><?= $rTitle ?></h3>
                        <p style="font-size:0.82rem;color:#64748b;line-height:1.6;margin:0;"><?= $rExcerpt ?>...</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// ── Reading progress ─────────────────────────────────
const progressBar = document.getElementById('reading-progress');
window.addEventListener('scroll', () => {
    const body   = document.body;
    const html   = document.documentElement;
    const total  = Math.max(body.scrollHeight, html.scrollHeight) - html.clientHeight;
    const pct    = (window.scrollY / total) * 100;
    progressBar.style.width = pct + '%';
});

// ── Table of contents ────────────────────────────────
const headings = document.querySelectorAll('.tm-post-body h2, .tm-post-body h3');
const tocList  = document.getElementById('toc-list');
const tocBox   = document.getElementById('toc-box');
if (headings.length < 2) { if(tocBox) tocBox.style.display='none'; }
headings.forEach((h, i) => {
    const id  = 'heading-' + i;
    h.id = id;
    const a   = document.createElement('a');
    a.href    = '#' + id;
    a.textContent = h.textContent;
    a.style.cssText = 'font-size:.82rem;color:#475569;text-decoration:none;line-height:1.4;padding:4px 0 4px ' + (h.tagName==='H3'?'16px':'0') + ';display:block;border-left:2px solid transparent;padding-left:12px;transition:color .15s,border-color .15s;';
    a.addEventListener('mouseenter', () => { a.style.color='#16a34a'; a.style.borderColor='#16a34a'; });
    a.addEventListener('mouseleave', () => { a.style.color='#475569'; a.style.borderColor='transparent'; });
    tocList.appendChild(a);
});

// ── Audio player ─────────────────────────────────────
<?php if($hasAudio && $audioUrl): ?>
const audio    = document.getElementById('audio-el');
const playBtn  = document.getElementById('audio-play');
const audioIcon= document.getElementById('audio-icon');
const progress = document.getElementById('audio-progress');
const currTime = document.getElementById('audio-current');
const durTime  = document.getElementById('audio-duration');

function fmt(s) {
    const m = Math.floor(s/60), sec = Math.floor(s%60);
    return m + ':' + String(sec).padStart(2,'0');
}
function toggleAudio() {
    if (audio.paused) {
        audio.play();
        audioIcon.className = 'fa-solid fa-pause';
    } else {
        audio.pause();
        audioIcon.className = 'fa-solid fa-play';
    }
}
audio.addEventListener('loadedmetadata', () => { durTime.textContent = fmt(audio.duration); });
audio.addEventListener('timeupdate', () => {
    if (audio.duration) {
        progress.value  = (audio.currentTime / audio.duration) * 100;
        currTime.textContent = fmt(audio.currentTime);
    }
});
audio.addEventListener('ended', () => { audioIcon.className = 'fa-solid fa-play'; progress.value = 0; });
progress.addEventListener('input', () => { audio.currentTime = (progress.value / 100) * audio.duration; });
<?php endif; ?>
</script>
