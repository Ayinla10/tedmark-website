<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Blog';
$pageDesc    = 'Business technology insights, guides, and case studies for African entrepreneurs and business leaders.';
$pageSeoPage = 'blog';

try {
    $posts = fetchAll("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT 12");
} catch(Exception $e){ $posts=[]; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Insights</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Business Technology<br>Insights for Africa</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:560px;margin:0 auto;line-height:1.7;">Practical guides, case studies, and ideas to help African businesses run smarter with technology.</p>
    </div>
</section>

<!-- BLOG -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <?php if(!empty($posts)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:28px;">
            <?php foreach($posts as $post): ?>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?>" style="text-decoration:none;display:block;" class="tm-card tm-fade">
                <div style="height:180px;border-radius:8px;margin:-24px -24px 20px;overflow:hidden;<?= empty($post['featured_image']) ? 'background:linear-gradient(135deg,#0f172a,#1e293b);display:flex;align-items:center;justify-content:center;' : '' ?>">
                    <?php if(!empty($post['featured_image'])): ?>
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;" alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php else: ?>
                    <i class="fa-solid fa-newspaper" style="font-size:2.5rem;color:#22c55e;opacity:0.4;"></i>
                    <?php endif; ?>
                </div>
                <div style="margin-bottom:10px;">
                    <span style="font-size:0.7rem;font-weight:700;color:#16a34a;letter-spacing:.08em;text-transform:uppercase;"><?= htmlspecialchars($post['category']??'Blog') ?></span>
                    <span style="color:#e2e8f0;margin:0 8px;">·</span>
                    <span style="font-size:0.7rem;color:#94a3b8;"><?= date('M j, Y', strtotime($post['published_at']??'now')) ?></span>
                </div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:10px;line-height:1.4;"><?= htmlspecialchars($post['title']) ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;"><?= htmlspecialchars(substr(strip_tags($post['excerpt']??''),0,130)) ?>...</p>
                <div style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:600;color:#16a34a;margin-top:14px;">Read article <i class="fa-solid fa-arrow-right fa-2xs"></i></div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- Placeholder posts when no DB -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:28px;">
            <?php
            $placeholders = [
                ['cat'=>'Business Systems','title'=>'5 Signs Your Business Needs a Proper Management System','excerpt'=>'If you\'re still tracking inventory in Excel or managing customers with WhatsApp messages, these signs will tell you it\'s time to upgrade.'],
                ['cat'=>'Automation','title'=>'How Automation Saved This Ghana Retailer 15 Hours a Week','excerpt'=>'A case study on how a Kumasi-based retailer went from manual invoicing to fully automated billing in just 4 weeks.'],
                ['cat'=>'Web Development','title'=>'Why Your Website Is Costing You Customers (And How to Fix It)','excerpt'=>'A slow, outdated website doesn\'t just look bad — it actively drives away potential customers. Here\'s how to fix that.'],
                ['cat'=>'Digital Marketing','title'=>'How African SMEs Can Win on Social Media Without a Big Budget','excerpt'=>'You don\'t need to spend millions on ads to build an engaged following. Here are proven strategies that work in African markets.'],
                ['cat'=>'E-Commerce','title'=>'Setting Up Mobile Money Payments for Your Online Store in Ghana','excerpt'=>'A step-by-step guide to integrating MTN MoMo and Vodafone Cash into your e-commerce store for higher conversion rates.'],
                ['cat'=>'Branding','title'=>'What Makes a Great African Brand? Lessons from Local Success Stories','excerpt'=>'The brands African consumers love most share common traits. Here\'s what your brand can learn from them.'],
            ];
            foreach($placeholders as $ph): ?>
            <div class="tm-card tm-fade">
                <div style="height:160px;background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:8px;margin:-24px -24px 20px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-newspaper" style="font-size:2rem;color:#22c55e;opacity:0.4;"></i>
                </div>
                <div style="margin-bottom:10px;">
                    <span style="font-size:0.7rem;font-weight:700;color:#16a34a;letter-spacing:.08em;text-transform:uppercase;"><?= $ph['cat'] ?></span>
                </div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:10px;line-height:1.4;"><?= $ph['title'] ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;"><?= $ph['excerpt'] ?></p>
                <div style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:600;color:#16a34a;margin-top:14px;">Coming soon <i class="fa-solid fa-clock fa-2xs"></i></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- NEWSLETTER -->
<section style="padding:80px 0;background:#fff;">
    <div class="tm-container">
        <div style="background:linear-gradient(135deg,#060b18,#0a1628);border-radius:20px;padding:52px;text-align:center;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-60px;left:-60px;width:200px;height:200px;background:radial-gradient(circle,rgba(34,197,94,0.1),transparent 70%);pointer-events:none;"></div>
            <div class="tm-label" style="justify-content:center;color:#22c55e;">Stay Updated</div>
            <h2 style="font-size:1.8rem;font-weight:900;color:#fff;margin:12px 0 12px;">Get Business Technology Insights</h2>
            <p style="color:#64748b;margin-bottom:28px;font-size:0.95rem;">Practical tips, case studies, and tools delivered to your inbox. No spam, ever.</p>
            <form id="newsletter-form" style="display:flex;gap:12px;max-width:440px;margin:0 auto;flex-wrap:wrap;">
                <input type="email" name="email" placeholder="your@email.com" class="tm-form-input" style="flex:1;min-width:200px;background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.15);color:#fff;" required>
                <button type="submit" class="tm-btn-primary" style="white-space:nowrap;">Subscribe <i class="fa-solid fa-paper-plane fa-xs"></i></button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
