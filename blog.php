<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Blog';
$pageDesc    = 'Business technology insights, guides, and case studies for entrepreneurs and business leaders.';
$pageSeoPage = 'blog';

try {
    $posts = fetchAll("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT 12");
} catch(Exception $e){ $posts=[]; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> Insights</div>
    <h1>Business Technology Insights</h1>
    <p>Practical guides, case studies, and ideas to help businesses run smarter with technology.</p>
</section>

<!-- BLOG -->
<section class="tm2-section">
    <div class="tm2-container">
        <?php if(!empty($posts)): ?>
        <div class="tm2-grid tm2-grid-3">
            <?php foreach($posts as $post): ?>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?>" style="text-decoration:none;display:block;" class="tm2-card">
                <div style="height:160px;border-radius:12px;margin:-24px -24px 18px;overflow:hidden;background:var(--bg-soft);display:flex;align-items:center;justify-content:center;">
                    <?php if(!empty($post['featured_image'])): ?>
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;" alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php else: ?>
                    <i class="fa-solid fa-newspaper" style="font-size:2rem;color:var(--accent);opacity:0.6;"></i>
                    <?php endif; ?>
                </div>
                <div style="margin-bottom:10px;">
                    <span style="font-size:0.7rem;font-weight:600;color:var(--accent);letter-spacing:.08em;text-transform:uppercase;"><?= htmlspecialchars($post['category']??'Blog') ?></span>
                    <span style="color:var(--border);margin:0 8px;">·</span>
                    <span style="font-size:0.7rem;color:var(--muted);"><?= date('M j, Y', strtotime($post['published_at']??'now')) ?></span>
                </div>
                <h3 style="margin-bottom:10px;"><?= htmlspecialchars($post['title']) ?></h3>
                <p><?= htmlspecialchars(substr(strip_tags($post['excerpt']??''),0,130)) ?>...</p>
                <div style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:500;color:var(--accent);margin-top:14px;">Read article <i class="fa-solid fa-arrow-right fa-2xs"></i></div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- Placeholder posts when no DB -->
        <div class="tm2-grid tm2-grid-3">
            <?php
            $placeholders = [
                ['cat'=>'Business Systems','title'=>'5 Signs Your Business Needs a Proper Management System','excerpt'=>'If you\'re still tracking inventory in Excel or managing customers with WhatsApp messages, these signs will tell you it\'s time to upgrade.'],
                ['cat'=>'Automation','title'=>'How Automation Saved This Ghana Retailer 15 Hours a Week','excerpt'=>'A case study on how a Kumasi-based retailer went from manual invoicing to fully automated billing in just 4 weeks.'],
                ['cat'=>'Web Development','title'=>'Why Your Website Is Costing You Customers (And How to Fix It)','excerpt'=>'A slow, outdated website doesn\'t just look bad, it actively drives away potential customers. Here\'s how to fix that.'],
                ['cat'=>'Digital Marketing','title'=>'How Small Businesses Can Win on Social Media Without a Big Budget','excerpt'=>'You don\'t need to spend millions on ads to build an engaged following. Here are proven strategies that work in any market.'],
                ['cat'=>'E-Commerce','title'=>'Setting Up Mobile Money Payments for Your Online Store in Ghana','excerpt'=>'A step-by-step guide to integrating MTN MoMo and Vodafone Cash into your e-commerce store for higher conversion rates.'],
                ['cat'=>'Branding','title'=>'What Makes a Great Brand? Lessons from Local Success Stories','excerpt'=>'The brands consumers love most share common traits. Here\'s what your brand can learn from them.'],
            ];
            foreach($placeholders as $ph): ?>
            <div class="tm2-card">
                <div style="height:140px;background:var(--bg-soft);border-radius:12px;margin:-24px -24px 18px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-newspaper" style="font-size:1.8rem;color:var(--accent);opacity:0.6;"></i>
                </div>
                <span style="font-size:0.7rem;font-weight:600;color:var(--accent);letter-spacing:.08em;text-transform:uppercase;"><?= $ph['cat'] ?></span>
                <h3 style="margin:8px 0 10px;"><?= $ph['title'] ?></h3>
                <p><?= $ph['excerpt'] ?></p>
                <div style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:500;color:var(--accent);margin-top:14px;">Coming soon <i class="fa-solid fa-clock fa-2xs"></i></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- NEWSLETTER -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-cta-band">
            <div class="tm2-eyebrow" style="justify-content:center;">Stay Updated</div>
            <h2 class="tm2-h2" style="margin-bottom:10px;">Get Business Technology Insights</h2>
            <p class="tm2-sub" style="margin-bottom:28px;">Practical tips, case studies, and tools delivered to your inbox. No spam, ever.</p>
            <form id="newsletter-form" class="tm2-email-form" style="margin:0 auto;">
                <input type="email" name="email" placeholder="your@email.com" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
